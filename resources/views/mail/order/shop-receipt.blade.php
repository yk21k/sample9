@component('mail::message')
# ご購入レシート

以下の内容でご注文を承りました。  

---

## ご購入商品
<table border="1" cellspacing="0" cellpadding="6" style="border-collapse: collapse; width:100%;">
    <thead>
        <tr>
            <th align="left">商品名</th>
            <th align="right">合計数量</th>
            <th align="right">単価</th>
            <th align="right">数量</th>
            <th align="right">単価(キャンペーンクーポン適用後)</th>
            <th align="right">数量(キャンペーンクーポン適用)</th>
            <th align="right">配送料</th>
            <th align="right">小計</th>
            <th align="right">消費税</th>
            <th align="right">合計</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($items_with_pricing as $item)
            <tr>
                <td align="left">{{ $item['product_name'] }}</td>
                <td align="right">{{ $item['quantity'] }}</td>
                <td align="right">{{ number_format($item['price']) }}円</td>
                <td align="right">{{ $item['quantity'] -1 }}</td>
                <td align="right">{{ number_format($item['final_price']) }}円</td>
                <td align="right">1</td>
                <td align="right">{{ number_format($item['shipping_fee']) }}円</td>
                <td align="right">{{ number_format($item['price'] * ($item['quantity'] -1) + ($item['final_price'] + $item['shipping_fee'])) }}円</td>
                <td align="right">{{ number_format(($item['price'] * ($item['quantity'] -1) + ($item['final_price'] + $item['shipping_fee'])) * 0.1)  }}円</td>
                <td align="right">{{ number_format(($item['price'] * ($item['quantity'] -1) + ($item['final_price'] + $item['shipping_fee'])) * 1.1) }}円</td>
            </tr>
        @endforeach
    </tbody>
</table>

---

@php
    $items_with_pricing = $mails->items_with_pricing ?? [];
    $subtotal = collect($items_with_pricing)->sum(function($item){
        // 割引適用は1個分
        $discounted = $item['final_price'];

        // 残りは通常価格
        $normal = $item['price'] * max(0, $item['quantity'] - 1);

        // 送料は数量分
        $shipping = $item['shipping_fee'];

        return $discounted + $normal + $shipping;
    });

    $totalTax = $subtotal * 0.1; // 消費税10%
    $totalPrice = $subtotal + $totalTax;
@endphp

- 商品合計＋送料：{{ number_format($subtotal) }}円  
- 消費税：{{ number_format($totalTax) }}円  
**合計：{{ number_format($totalPrice) }}円**


---

@component('mail::button', ['url' => route('home')])
ショップに戻る
@endcomponent

ご利用ありがとうございました。  
{{ config('app.name') }}
@endcomponent
