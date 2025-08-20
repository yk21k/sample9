@extends('layouts.seller')

@section('content')
<style>
    .slip-container {
        width: 90%;
        margin: 20px auto;
        font-family: "游ゴシック", "Hiragino Kaku Gothic ProN", sans-serif;
        border: 2px solid #333;
        padding: 15px;
        background: #848484;
    }
    .slip-header {

        font-size: 18px;
        font-weight: bold;
        margin-bottom: 10px;
    }
    .slip-table {

        width: 100%;
        border-collapse: collapse;
        margin-bottom: 20px;
    }
    .slip-table th, .slip-table td {
        border: 1px solid #ccc;
        padding: 6px 8px;
        font-size: 14px;
        text-align: center;
    }
    .slip-table th {
        background: #a4a4a4;
    }
    .total-row {
        font-weight: bold;
        background: #a4a4a4;
    }
</style>

<div class="slip-container">
    <h2>伝票</h2>
    @foreach($sales as $sale)
        <div class="slip-header">
            注文番号: {{ $sale->order_id }}　
            　<small>出品者: {{ $sale->seller_name }}&nbsp;{{ $sale->shop_location }}</small><br>
            登録番号: {{ $sale->seller_registration_number }}
        </div>

        <table class="slip-table">
            <thead>
                <tr>
                    <th>商品名</th>
                    <th>数量</th>
                    <th>単価(税抜)</th>
                    <th>税率(%)</th>
                    <th>消費税額</th>
                    <th>数量</th>
                    <th>配送料</th>
                    <th>配送料消費税</th>
                    <th>税込金額</th>
                    <th>税区分</th>
                </tr>
            </thead>
            <tbody>
                @foreach($sale->items as $item)
                <tr>
                    <td>{{ $item->product_name }}</td>
                    <td>{{ $item->quantity }}</td>
                    <td>{{ number_format($item->unit_price) }}</td>
                    <td>{{ $item->tax_rate }}</td>
                    <td>{{ number_format($item->tax_amount) }}</td>
                    <td>{{ $item->quantity }}</td>
                    <td>{{ number_format($item->shipping_fee) }}</td>
                    <td>{{ number_format($item->shipping_fee_tax_amount) }}</td>
                    <td>{{ number_format($item->total_amount) }}</td>
                    <td>{{ $item->tax_category }}</td>
                </tr>
                @endforeach
                <tr class="total-row">
                    <td>合計</td>
                    <td>{{ $sale->order_totals->quantity_total }}</td>
                    <td>{{ number_format($sale->order_totals->unit_price_total) }}</td>
                    <td>-</td>
                    <td>{{ number_format($sale->order_totals->tax_total) }}</td>
                    <td>-</td>
                    
                    <td>{{ number_format($sale->order_totals->shipping_fee_total) }}</td>
                    <td>{{ number_format($sale->order_totals->shipping_fee_tax_total) }}</td>

                    <td>{{ number_format($sale->order_totals->grand_total) }}</td>
                    <td>-</td>
                </tr>
            </tbody>
        </table>
    @endforeach
</div>

@endsection
