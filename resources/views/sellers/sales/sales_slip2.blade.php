@extends('layouts.seller')

@section('content')
<style>
    .receipt-table {
        width: 100%;
        border-collapse: collapse;
        font-family: monospace; /* レシート風に */
        font-size: 14px;
        background-color: #fff;
        color: #000;
    }
    .receipt-table th,
    .receipt-table td {
        border: 1px solid #000;
        padding: 4px 6px;
        text-align: left;
        vertical-align: top;
    }
    .receipt-table th {
        font-weight: bold;
        text-align: center;
    }
    .receipt-total {
        font-weight: bold;
        background-color: #f8f8f8;
    }
</style>
<p><strong>※ 取引日(到着確認日)が「未確認」と表示されている場合は、当システムを利用して送付できません。</strong></p>
<table class="receipt-table" border="1" cellpadding="5" cellspacing="0" style="border-collapse: collapse; width: 100%;">
    <thead>
        <tr>
            <th>購入日</th>
            <th>取引日(到着確認日)</th>
            <th>注文番号</th>
            <th>出品者名称</th>
            <th>出品者登録番号</th>
            <th>商品名</th>
            <th>数量</th>
            <th>単価(税抜)</th>
            <th>適用ラベル</th>
            <th>配送料(税抜)</th>
            <th>消費税額</th>
            <th>合計金額</th>
            <th>税区分</th>
        </tr>
    </thead>
    <tbody>
        @foreach($sales as $sale)
            @php
                $tax_rate = 0;
                $shopInvoice = '免税';
                if(isset($sale->items[0]['tax_category']) && $sale->items[0]['tax_category']) {
                    $tax_rate = \App\Models\TaxRate::current()?->rate ?? 0;
                    $shopInvoice = $sale->invoice_number;
                    $tax_cat = "課税 (" . ($tax_rate * 100) . "%)";

                }

            @endphp
            
            @foreach($sale->items as $item)
                {{-- 1個目割引（キャンペーン・クーポン適用） --}}
                <tr>
                    <td>{{ $sale->purchase_date->format('Y-m-d') }}</td>
                    <td>{{ $sale->confirmed_at ? $sale->confirmed_at->format('Y-m-d') : '未確認' }}</td>
                    <td>{{ $sale->order_id }}</td>
                    <td>{{ $sale->shop_name }}</td>
                    <td>{{ $shopInvoice }}</td>
                    <td>{{ $item['product_name'] }}</td>
                    <td>1</td>
                    <td>{{ number_format($item['lowest_price']) }}</td>
                    <td>{{ $item['applied_label'] }}</td>
                    <td>{{ number_format($item['shipping_fee']) }}</td>
                    <td>{{ number_format(floor(($item['lowest_price'] + $item['shipping_fee']) * $tax_rate)) }}</td>
                    <td>{{ number_format($item['lowest_price'] + $item['shipping_fee'] + floor(($item['lowest_price'] + $item['shipping_fee'])*$tax_rate)) }}</td>
                    <td>{{ $tax_cat }}</td>
                </tr>

                {{-- 2個目以降通常価格 --}}
                @if($item['quantity'] > 1)
                    <tr>
                        <td>{{ $sale->purchase_date->format('Y-m-d') }}</td>
                        <td>{{ $sale->confirmed_at ? $sale->confirmed_at->format('Y-m-d') : '未確認' }}</td>
                        <td>{{ $sale->order_id }}</td>
                        <td>{{ $sale->shop_name }}</td>
                        <td>{{ $shopInvoice }}</td>
                        <td>{{ $item['product_name'] }}</td>
                        <td>{{ $item['quantity'] - 1 }}</td>
                        <td>{{ number_format($item['unit_price']) }}</td>
                        <td>通常価格</td>
                        <td>{{ number_format($item['shipping_fee'] * ($item['quantity'] -1)) }}</td>
                        <td>{{ number_format(floor(($item['unit_price']*($item['quantity']-1) + $item['shipping_fee']*($item['quantity']-1)) * $tax_rate)) }}</td>
                        <td>{{ number_format(($item['unit_price']*($item['quantity']-1) + $item['shipping_fee']*($item['quantity']-1)) + floor(($item['unit_price']*($item['quantity']-1) + $item['shipping_fee']*($item['quantity']-1))*$tax_rate)) }}</td>
                        <td>{{ $tax_cat }}</td>
                    </tr>
                @endif
            @endforeach

            {{-- 合計行 --}}
            @php
                $total_price = collect($sale->items)->sum(function($i) use ($tax_rate) {
                    $total_item_price = $i['lowest_price'] + $i['shipping_fee'];
                    $remaining_total = ($i['quantity'] > 1) ? ($i['unit_price'] * ($i['quantity']-1) + $i['shipping_fee']*($i['quantity']-1)) : 0;
                    return $total_item_price + $remaining_total + floor(($total_item_price + $remaining_total)*$tax_rate);
                });
                $total_tax = collect($sale->items)->sum(function($i) use ($tax_rate) {
                    $total_item_tax = floor(($i['lowest_price'] + $i['shipping_fee'])*$tax_rate);
                    $remaining_tax = ($i['quantity'] >1) ? floor(($i['unit_price']*($i['quantity']-1)+$i['shipping_fee']*($i['quantity']-1))*$tax_rate) : 0;
                    return $total_item_tax + $remaining_tax;
                });
            @endphp
            <tr>
                <td style="font-weight:bold;">合計</td>
                <td>消費税：{{ number_format($total_tax) }}</td>
                <td>合計価格：{{ number_format($total_price) }}</td>
                <td>{{ $tax_cat }}</td>
            </tr>
        @endforeach
    </tbody>
</table>


@endsection


