<?php

namespace App\Http\Controllers;

use App\Models\LineItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class LineItemController extends Controller
{
    public function create(Request $request)
    {
        // セッションにあるカートを取得
        $cart_id = Session::get('cart');
        // 追加した商品がすでにカートに入っているか確認
        $line_item = LineItem::whereCartId($cart_id)
            ->whereProductId($request->input('id'))
            ->first();

        if ($line_item) {
            // 追加した商品がカートに存在した場合は元の個数に追加した個数を足して保存
            $line_item->quantity += $request->input('quantity');
            $line_item->save();
        } else {
            // line_itemsテーブルにレコードを加え
            LineItem::create([
                'cart_id' => $cart_id,
                'product_id' => $request->input('id'),
                'quantity' => $request->input('quantity'),
            ]);
        }

        return redirect(route('cart.index'));
    }

    public function delete(Request $request)
    {
        LineItem::destroy($request->input('id'));

        return redirect(route('cart.index'));
    }
}
