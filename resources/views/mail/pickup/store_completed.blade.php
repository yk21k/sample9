@component('mail::message')
# 受け渡し完了のお知らせ

{{ $user->name }} 様

以下の商品について、店舗にて受け渡しが完了しました。

---

## ■ 商品情報
- 商品名：**{{ $item->product->name }}**

@php
    use App\Models\Shop;

    $product = $item->product;
    $shop = Shop::find($product->shop_id);
@endphp

@if($shop && $shop->invoice_number)
- 価格：**{{ number_format($product->price * 1.1) }}（税込）**
@else
- 価格：**{{ number_format($product->price) }}（税抜）**
@endif

- 個数：**1個**
- 注文番号：{{ $order->id }}
- 受取日時：{{ now()->format('Y/m/d H:i') }}

---

ご利用ありがとうございました。  
またのご利用をお待ちしております。

もしお客様からの受取確認を当サイトのお客様用のページからされていない場合は、こちらのメールを持って受取確認されたといたしまして手続きを進めさせていただきます。

@endcomponent
