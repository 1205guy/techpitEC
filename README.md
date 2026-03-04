# EC Platform (Laravel 11 & Stripe)

Laravel 11 と Stripe Checkout を使用した、EC機能のバックエンド実装です。
セッションベースのカート管理、多対多のリレーション、および外部決済APIとの連携を実装しています。

## 主な機能

* **カートセッション**: ミドルウェアによる自動カート生成およびIDのセッション管理
* **カート操作**: 商品の追加（既存品がある場合は数量加算）、および削除機能
* **決済連携**: Stripe API を利用した、サーバーサイドでの決済セッション作成
* **データ管理**: 決済完了後、対象カートに紐づく明細データ（LineItem）の自動削除
* **リレーション**: `belongsToMany` を用いた Cart と Product の多対多接続

## 技術スタック

* **Backend**: Laravel 11.x
* **Payment**: Stripe API
* **Database**: MySQL / SQLite（中間テーブル `line_items` を使用）

## ルーティング

### 商品閲覧

| Method | Endpoint | Description |
| --- | --- | --- |
| GET | `/product` | 商品一覧の表示 |
| GET | `/product/{id}` | 商品詳細の表示 |

### カート・明細操作

| Method | Endpoint | Description |
| --- | --- | --- |
| POST | `/line_item/create` | カートへ商品追加（既存時は数量加算） |
| POST | `/line_item/delete` | カート内の特定明細を削除 |

### 決済フロー

| Method | Endpoint | Description |
| --- | --- | --- |
| GET | `/cart` | カート内一覧の取得 |
| GET | `/cart/checkout` | Stripe 決済セッションの作成 |
| GET | `/cart/success` | 決済完了後のデータクリーンアップ |

