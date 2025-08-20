@extends('layouts.seller')

@section('content')

<style>
    .slip-container {
        width: 1000px;
        margin: 10px auto;
        font-family: "游ゴシック", "ヒラギノ角ゴ ProN", sans-serif;
        border: 1px solid #333;
        padding: 10px;
        background: #848484;

    }
    .order-header {
        font-size: 18px;
        font-weight: bold;
        margin-bottom: 5px;
        border-bottom: 2px solid #333;
        padding-bottom: 2px;
    }
    table.slip-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 10px;
    }
    table.slip-table th, table.slip-table td {
        border: 1px solid #666;
        padding: 6px 10px;
        text-align: left;
        font-size: 14px;
    }
    table.slip-table th {
        background: #848484;
    }
    .order-total {
        text-align: right;
        font-weight: bold;
        background: #a4a4a4;
    }
</style>

<div class="slip-container">
    <h2>売上伝票一覧</h2>

    @foreach ($sales as $sale)
        <div class="order-block border p-4 mb-6 rounded">
            <h3>注文番号: {{ $sale->order_id }}</h3>
            <p>購入日: {{ $sale->purchase_date }}</p>
            <p>出品者: {{ $sale->seller_name }} (登録番号: {{ $sale->seller_registration_number }})</p>
            @php
                $totalQty = 0;
                $totalPrice = 0;
                $totalPriceWithTax = 0;
                $totalShipping = 0;
                $totalShippingWithTax = 0;
                $grandTotal = 0;
            @endphp
            <table class="w-full border mt-3">
                <thead>
                    <tr class="bg-gray-200">
                        <th class="p-2 border">商品名</th>
                        <th class="p-2 border">数量</th>
                        <th class="p-2 border">単価</th>
                        <th class="p-2 border">税込</th>
                        <th class="p-2 border">数量</th>
                        <th class="p-2 border">配送料</th>
                        <th class="p-2 border">配送料(税込)</th>
                        <th class="p-2 border">小計(税込)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($sale->items as $item)
                        @php
                            $originalQty = $item['quantity'];
                            $unitPrice   = $item['unit_price'];
                            $shippingFee   = $item['shipping_fee'];

                            // --- キャンペーン判定（最大割引率） ---
                            $shop = App\Models\Shop::where('user_id', auth()->user()->id)->first();
                            $campaign = $shop
                                ? App\Models\Campaign::where('shop_id', $shop->id)
                                    ->where('start_date', '<=', $sale->purchase_date)
                                    ->where('end_date', '>=', $sale->purchase_date)
                                    ->orderByDesc('dicount_rate1')
                                    ->first()
                                : null;

                            $campaignPrice = $campaign ? $unitPrice - ($unitPrice * $campaign->dicount_rate1) : null;

                            // --- クーポン判定 ---
                            $coupon = App\Models\ShopCoupon::where('code', $sale->coupon_code ?? '')->first();
                            $couponPrice = $coupon ? $unitPrice + $coupon->value : null;

                            // --- 適用価格決定（最も安い方を採用） ---
                            $finalPrice = $unitPrice;
                            $discountLabel = null;
                            if ($campaignPrice !== null && $couponPrice !== null) {
                                if ($campaignPrice < $couponPrice) {
                                    $finalPrice = $campaignPrice;
                                    $discountLabel = 'キャンペーン適用';
                                } else {
                                    $finalPrice = $couponPrice;
                                    $discountLabel = 'クーポン適用';
                                }
                            } elseif ($campaignPrice !== null) {
                                $finalPrice = $campaignPrice;
                                $discountLabel = 'キャンペーン適用';
                            } elseif ($couponPrice !== null) {
                                $finalPrice = $couponPrice;
                                $discountLabel = 'クーポン適用';
                            }

                            // --- 行の分割 ---
                            $rows = [];
                            if ($discountLabel) {
                                // 割引は1個だけ適用
                                $normalQty = $originalQty - 1;
                                if ($normalQty > 0) {
                                    $rows[] = [
                                        'name' => $item['product_name'],
                                        'qty' => $normalQty,
                                        'shiping_fee' => $shippingFee,

                                        'price' => $unitPrice,
                                        'total' => ($unitPrice + $shippingFee) * $normalQty * 1.1
                                    ];
                                }
                                $rows[] = [
                                    'name' => $item['product_name'] . "（{$discountLabel}）",
                                    'qty' => 1,
                                    'shiping_fee' => $shippingFee,
                                    'price' => $finalPrice,
                                    'total' => ($finalPrice + $shippingFee) * 1.1
                                ];
                            } else {
                                $rows[] = [
                                    'name' => $item['product_name'],
                                    'qty' => $originalQty,
                                    'shiping_fee' => $shippingFee,

                                    'price' => $unitPrice,
                                    'total' => ($unitPrice + $shippingFee) * $originalQty * 1.1
                                ];
                            }
                        @endphp

                        @foreach ($rows as $row)
                                @php
                                    $totalQty += $row['qty'];
                                    $totalPrice += $row['price'] * $row['qty'];
                                    $totalPriceWithTax += $row['price'] * $row['qty'] * 1.1;
                                    $totalShipping += $row['shiping_fee'] * $row['qty'];
                                    $totalShippingWithTax += $row['shiping_fee'] * $row['qty'] * 1.1;
                                    $grandTotal += $row['total'];
                                @endphp
                            <tr>
                                <td class="p-2 border">{{ $row['name'] }}</td>
                                <td class="p-2 border">{{ $row['qty'] }}</td>

                                <td class="p-2 border">{{ number_format($row['price']) }}円</td>
                                <td class="p-2 border">{{ number_format($row['price']*1.1) }}円</td>
                                <td class="p-2 border">{{ $row['qty'] }}</td>

                                <td class="p-2 border">{{ $row['shiping_fee']*$row['qty']  }}円</td>
                                <td class="p-2 border">{{ $row['shiping_fee']*$row['qty'] *1.1 }}円</td>

                                <td class="p-2 border">{{ number_format($row['total']) }}円</td>
                            </tr>
                        @endforeach
                    @endforeach
                    <tr class="font-bold bg-gray-200">
                        <td>合計</td>
                        <td>{{ $totalQty }}</td>
                        <td>{{ number_format($totalPrice) }}円</td>
                        <td>{{ number_format($totalPriceWithTax) }}円</td>
                        <td>---</td>
                        <td>{{ number_format($totalShipping) }}円</td>
                        <td>{{ number_format($totalShippingWithTax) }}円</td>
                        <td>{{ number_format($grandTotal) }}円</td>
                    </tr>
                </tbody>
            </table>

            {{-- 注文合計 --}}
            <div class="mt-3 text-right">
                <p>商品合計: {{ number_format($totalPrice) }}円</p>

                <p>消費税: {{ number_format($totalPrice*0.1) }}円</p>

                <p>送料: {{ number_format($totalShipping) }}円</p>
                <p>消費税: {{ number_format($totalShipping*0.1) }}円</p>

                <p class="font-bold">総合計: {{ number_format($grandTotal) }}円</p>

            </div>
        </div><br>
    @endforeach
</div>



@endsection


