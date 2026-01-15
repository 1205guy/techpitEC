<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\LineItem;
use Illuminate\Support\Facades\Session;
use Stripe\Checkout\Session as StripeSession;
use Stripe\Stripe;

class CartController extends Controller
{
    public function index()
    {
        $cart_id = Session::get('cart');
        $cart = Cart::findOrFail($cart_id);

        $total = 0;
        // カートに入っている合計金額を算出
        foreach ($cart->products as $product) {
            $total += $product->price * $product->pivot->quantity;
        }
        $line_items = $cart->products;
        $total_price = $total;

        return view('cart.index', compact('line_items', 'total_price'));
    }

    public function checkout()
    {
        $cart_id = Session::get('cart');
        $cart = Cart::with('products')->find($cart_id);

        // カートの中身がNullだったら、リダイレクト
        if (count($cart->products) <= 0) {
            return redirect(route('cart.index'));
        }

        // $line_items = [];
        // foreach ($cart->products as $product) {
        //     $line_item = [
        //         'price_data' => [
        //             'currency' => 'jpy',
        //             'unit_amount' => $product->price,
        //             'product_data' => [
        //                 'name' => $product->name,
        //                 'description' => $product->description,
        //             ],
        //         ],
        //         'quantity' => $product->pivot->quantity,
        //     ];
        //     array_push($line_items, $line_item);
        // }

        $lineItem = $cart->products->map(function ($product) {
            return [
                'price_data' => [
                    'currency' => 'jpy',
                    'unit_amount' => $product->price,
                    'product_data' => [
                        'name' => $product->name,
                        'description' => $product->description,
                    ],
                ],
                'quantity' => $product->pivot->quantity,
            ];
        })->toArray();

        // Stripeの設定（config経由で取得）
        Stripe::setApiKey(config('services.stripe.secret'));

        // 決済セッション作成
        $checkoutSession = StripeSession::create([
            'payment_method_types' => ['card'],
            'line_items' => $lineItem,
            'mode' => 'payment',
            'success_url' => route('cart.success'),
            'cancel_url' => route('cart.index'),
            'billing_address_collection' => 'required',
        ]);

        return view('cart.checkout', [
            'session' => $checkoutSession,
            'publicKey' => config('services.stripe.key'),
        ]);
    }

    public function success()
    {
        $cart_id = Session::get('cart');
        LineItem::whereCartId($cart_id)->delete();

        return redirect(route('product.index'));
    }
}
