@extends('layouts.seller')

@section('content')

<div class="container">
    <h1>売上管理（インボイス対応）</h1>

	<div class="mb-3">
	    <form method="POST" action="{{ route('seller.orders.invoice') }}" style="display:inline">
	        @csrf
	        <input type="hidden" name="format" value="csv">
	        <button type="submit" class="btn btn-success">CSV出力</button>
	    </form>
        <form method="POST" action="{{ route('seller.orders.invoice2') }}" style="display:inline">
            @csrf
            <input type="hidden" name="format" value="csv">
            <button type="submit" class="btn btn-danger">CSV出力</button>
        </form>
        <a href=" {{route('seller.order.sales_order_invoice_slip')}} " class="list-group-item list-group-item-action">伝票形式</a>
        <a href=" {{route('seller.order.sales_order_invoice_slip2')}} " class="list-group-item list-group-item-action">伝票形式2</a>
	</div>

    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>購入日</th>
                <th>取引日(到着確認日)</th>
                <th>入金日(手数料以外)</th>
                <th>注文番号</th>
                <th>出品者名称</th>
                <th>出品者登録番号</th>
                <th>商品名</th>
                <th>数量</th>
                <th>単価（税抜）</th>
                <th>税率(%)</th>
                <th>消費税額</th>
                <th>単価（税抜）割引対象</th>
                <th>税率(%)</th>
                <th>消費税額</th>

                <th>配送料（税抜）</th>
                <th>税率(%)</th>
                <th>消費税額</th>
                <th>税込金額</th>
                <th>税込金額(配送料)</th>

                <th>消費税込み合計</th>
                <th>税区分</th>
            </tr>
        </thead>
        <tbody>

            @forelse ($sales as $sale)
                @php
                    $original_price = $sale->unit_price;
                    $shipFee = $sale->shipping_fee;
                    $shipping_fee_tax = $shipFee *0.1;

                    $shop_coupon = App\Models\ShopCoupon::where('code', $sale->coupon_code)->first();

                    $shop_campaign_pre = App\Models\Shop::where('user_id', $sale->seller_id)->first();

                    //$sale->purchase_date 購入日で期間を確認する

                    $campaign_set = App\Models\Campaign::where('shop_id', $shop_campaign_pre->id)->where('start_date', '<=', $sale->purchase_date)
                        ->where('end_date', '>=', $sale->purchase_date)
                        ->orderByDesc('dicount_rate1')
                        ->first();

                    $campaign_set_price = null; // 初期化しておくと安全
                    if($campaign_set){
                        if($sale->quantity >= 2){
                            $campaign_set_price = $sale->unit_price - ($sale->unit_price * $campaign_set->dicount_rate1);
                            $campaign_set_price_tax = $campaign_set_price * 0.1;
                            $campaign_set_price_remove_total = $sale->unit_price * ($sale->quantity - 1);
                            $campaign_total = $campaign_set_price+($sale->unit_price*($sale->quantity-1));
                        }                        
                    }

                    $coupon_set = null; // 初期化しておくと安全
                    if(isset($shop_coupon)){
                        if($sale->quantity >= 2){
                            $coupon_set = $sale->unit_price + $shop_coupon->value;
                            $coupon_set_tax = $coupon_set * 0.1;
                            $coupon_set_remove_total = $sale->unit_price * ($sale->quantity - 1);
                        }                        
                    }

                    $prices = array_filter([
                        $original_price,
                        $campaign_set_price,
                        $coupon_set
                    ], fn($v) => $v !== null);

                    $lowest_price = !empty($prices) ? min($prices) : null;

                    $campaign_total = floor((
                        $campaign_set_price +
                        $sale->unit_price * ($sale->quantity - 1) +
                        ($shipFee * $sale->quantity)
                    ) * 1.1);

                    $couponTotal = floor((
                        $coupon_set +
                        $sale->unit_price * ($sale->quantity - 1) +
                        ($shipFee * $sale->quantity)
                    ) * 1.1);

                @endphp
                <tr>
                    <td>{{ $sale->purchase_date ? $sale->purchase_date->format('Y-m-d') : '未確認' }} </td>

                    <!-- 到着確認日（売上確定日） -->
                    <td>{{ $sale->confirmed_at ? $sale->confirmed_at->format('Y-m-d') : '未確認' }}</td>


                    <!-- 送金日（入金日） -->
                    <td>{{ $sale->pay_transfer ? $sale->pay_transfer->format('Y-m-d') : '未確認'  }}</td>

                    <td>{{ $sale->order_number }}</td>
                    <td>{{ $sale->seller_name }}</td>   
                    <td>{{ $sale->seller_registration_number }}</td>
                    <td>{{ $sale->product_name }}</td>
                    <td>{{ $sale->quantity }}</td>

                    

                    @if($lowest_price !== $sale->unit_price && $lowest_price == $campaign_set_price)
                        <td>{{ number_format($sale->unit_price, 0) }}</td>
                        <td>{{ $sale->tax_rate }}</td>
                        <td>{{ $sale->unit_price*0.1 }}</td>

                        <td>{{ number_format($campaign_set_price, 0) }}(キャンペーン適用価格)</td>
                        <td>{{ $sale->tax_rate }}</td>
                        <td>{{ number_format($campaign_set_price_tax, 0) }}</td>

                        <td>{{ number_format($sale->shipping_fee, 0) }}</td>
                        <td>{{ $sale->tax_rate }}</td>
                        <td>{{ number_format($shipping_fee_tax, 0) }}</td>
                        <td>{{ number_format($sale->shipping_fee*1.1, 0) }}</td>
                        <td>{{ number_format($sale->shipping_fee*$sale->quantity*1.1, 0) }}</td>

                        <td>{{number_format($campaign_total, 0) }}</td>



                    @elseif($lowest_price !== $sale->unit_price && $lowest_price == $coupon_set)
                        <td>{{ number_format($sale->unit_price, 0) }}</td>
                        <td>{{ $sale->tax_rate }}</td>
                        <td>{{ $sale->unit_price*0.1 }}</td>

                        <td>{{ number_format($coupon_set, 0) }}(クーポン適用価格)</td>
                        <td>{{ $sale->tax_rate }}</td>
                        <td>{{ number_format($coupon_set_tax, 0) }}</td>

                        
                        <td>{{ number_format($sale->shipping_fee, 0) }}</td>
                        <td>{{ $sale->tax_rate }}</td>
                        <td>{{ number_format($shipping_fee_tax, 0) }}</td>
                        <td>{{ number_format($sale->shipping_fee*1.1, 0) }}</td>
                        <td>{{ number_format($sale->shipping_fee*$sale->quantity*1.1, 0) }}</td>

                        <td>{{number_format($couponTotal, 0) }}</td>
                                                
                    @endif




                    <td>{{ $sale->tax_category }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="12" class="text-center">データがありません</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
