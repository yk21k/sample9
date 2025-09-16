@extends('layouts.seller')

@section('content')

<div class="container">
    <h1>売上管理（インボイス対応）</h1>

	<div class="mb-3">
	    
        <form method="POST" action="{{ route('seller.orders.invoice2') }}" style="display:inline">
            @csrf
            <input type="hidden" name="format" value="csv">
            <button type="submit" class="btn btn-success">CSV出力</button>
        </form>
        
        <a href=" {{route('seller.order.sales_order_invoice_slip2')}} " class="list-group-item list-group-item-action">伝票形式</a>
	</div>
    <div class="table-responsive">
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th style="white-space: nowrap;">購入日</th>
                    <th style="white-space: nowrap;">取引日(到着確認日)</th>
                    <th style="white-space: nowrap;">入金日(手数料以外)</th>
                    <th style="white-space: nowrap;">注文番号</th>
                    <th style="white-space: nowrap;">出品者名称</th>
                    <th style="white-space: nowrap;">出品者登録番号</th>
                    <th style="white-space: nowrap;">商品名</th>
                    <th style="white-space: nowrap;">数量</th>
                    <th style="white-space: nowrap;">単価（税抜）</th>
                    <th style="white-space: nowrap;">税率(%)</th>
                    <th style="white-space: nowrap;">消費税額</th>
                    <th style="white-space: nowrap;">単価（税抜）割引対象</th>
                    <th style="white-space: nowrap;">税率(%)</th>
                    <th style="white-space: nowrap;">消費税額</th>

                    <th style="white-space: nowrap;">配送料（税抜）</th>
                    <th style="white-space: nowrap;">税率(%)</th>
                    <th style="white-space: nowrap;">消費税額</th>
                    <th style="white-space: nowrap;">税込金額</th>
                    <th style="white-space: nowrap;">配送料合計</th>

                    <th style="white-space: nowrap;">合計</th>
                    <th style="white-space: nowrap;">手数料(消費税込)</th>
                    <th style="white-space: nowrap;">消費税(手数料)</th>

                    <th style="white-space: nowrap;">税区分</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($sales as $sale)
                    @php
                        $tax_rate = \App\Models\TaxRate::current()?->rate ?? 0;
                        $fee_rate = \App\Models\Commition::current()?->rate ?? 0;

                        $original_price = $sale->unit_price;

                        $fee = $original_price * $sale->quantity * $fee_rate;
                        $fee_tax = $fee * $tax_rate;

                        $shipFee = $sale->shipping_fee;
                        $shipping_fee_tax = $shipFee * $tax_rate;

                        // クーポンコードを分解
                        $couponCodes = explode(',', $sale->coupon_code);

                        // 複数クーポンを一気に取得
                        $shop_coupons = App\Models\ShopCoupon::whereIn('code', $couponCodes)->get();


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
                                $campaign_set_price_tax = $campaign_set_price * $tax_rate;
                                $campaign_set_price_remove_total = $sale->unit_price * ($sale->quantity - 1);
                                $campaign_total = $campaign_set_price+($sale->unit_price*($sale->quantity-1));
                            }                        
                        }

                        $coupon_prices = []; // 複数の候補を格納する配列

                        foreach ($shop_coupons as $coupon) {
                            // 対象商品か確認
                            if ($coupon->product->id === $sale->product_id) {
                                // 数量条件を確認
                                if ($sale->quantity >= 2) {
                                    // クーポン適用後の価格を計算
                                    $coupon_set = $sale->unit_price + $coupon->value;
                                    $coupon_set_tax = $coupon_set * $tax_rate;
                                    $coupon_prices[] = max(0, $coupon_set); // 0未満にならないように
                                }
                            }
                        }

                        // 比較用の最終値（1件もなければ null）
                        $coupon_set = !empty($coupon_prices) ? min($coupon_prices) : null;

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
                        ) * ($tax_rate + 1));

                        $couponTotal = floor((
                            $coupon_set +
                            $sale->unit_price * ($sale->quantity - 1) +
                            ($shipFee * $sale->quantity)
                        ) * ($tax_rate + 1));

                    @endphp
                    @if($sale->tax_category === '課税業者')
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
                                <td>{{ $sale->unit_price*$tax_rate }}</td>

                                <td>{{ number_format($campaign_set_price, 0) }}(キャンペーン適用価格)</td>
                                <td>{{ $sale->tax_rate }}</td>
                                <td>{{ number_format($campaign_set_price_tax, 0) }}</td>

                                <td>{{ number_format($sale->shipping_fee, 0) }}</td>
                                <td>{{ $sale->tax_rate }}</td>
                                <td>{{ number_format($shipping_fee_tax, 0) }}</td>
                                <td>{{ number_format($sale->shipping_fee*($tax_rate + 1), 0) }}</td>
                                <td>{{ number_format($sale->shipping_fee*$sale->quantity*($tax_rate + 1), 0) }}</td>

                                <td>{{number_format($campaign_total, 0) }}</td>



                            @elseif($lowest_price !== $sale->unit_price && $lowest_price == $coupon_set)
                                <td>{{ number_format($sale->unit_price, 0) }}</td>
                                <td>{{ $sale->tax_rate }}</td>
                                <td>{{ $sale->unit_price*$tax_rate }}</td>

                                <td>{{ number_format($coupon_set, 0) }}(クーポン適用価格)</td>
                                <td>{{ $sale->tax_rate }}</td>
                                <td>{{ number_format($coupon_set_tax, 0) }}</td>

                                
                                <td>{{ number_format($sale->shipping_fee, 0) }}</td>
                                <td>{{ $sale->tax_rate }}</td>
                                <td>{{ number_format($shipping_fee_tax, 0) }}</td>
                                <td>{{ number_format($sale->shipping_fee*($tax_rate + 1), 0) }}</td>
                                <td>{{ number_format($sale->shipping_fee*$sale->quantity*($tax_rate + 1), 0) }}</td>

                                <td>{{number_format($couponTotal, 0) }}</td>
                            @else    
                                <td>{{ number_format($sale->unit_price, 0) }}</td>
                                <td>{{ $sale->tax_rate }}</td>
                                <td>{{ $sale->unit_price*$tax_rate }}</td>
                                <td>クーポン適用なし</td>
                                <td>---</td>
                                <td>---</td>

                                <td>{{ number_format($sale->shipping_fee, 0) }}</td>
                                <td>{{ $sale->tax_rate }}</td>
                                <td>{{ number_format($shipping_fee_tax, 0) }}</td>
                                <td>{{ number_format($sale->shipping_fee*($tax_rate + 1), 0) }}</td>
                                <td>{{ number_format($sale->shipping_fee*$sale->quantity*($tax_rate + 1), 0) }}</td>
                                <td>{{ number_format(($sale->unit_price + $sale->shipping_fee)*$sale->quantity*($tax_rate + 1), 0) }}</td>
                                                           
                            @endif
                            <td>{{ $fee }}</td>
                            <td>{{ $fee_tax  }}</td>
                            <td>{{ $sale->tax_category }}</td>
                        </tr>
                    @else
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
                                <td>-</td>

                                <td>{{ number_format($campaign_set_price, 0) }}(キャンペーン適用価格)</td>
                                <td>{{ $sale->tax_rate }}</td>
                                <td>-</td>

                                <td>{{ number_format($sale->shipping_fee, 0) }}</td>
                                <td>{{ $sale->tax_rate }}</td>
                                <td>-</td>
                                <td>{{ number_format($sale->shipping_fee, 0) }}</td>
                                <td>{{ number_format($sale->shipping_fee*$sale->quantity, 0) }}</td>

                                <td>{{number_format(($campaign_set_price + $sale->unit_price * ($sale->quantity - 1) + ($shipFee * $sale->quantity)), 0) }}</td>


                            @elseif($lowest_price !== $sale->unit_price && $lowest_price == $coupon_set)
                                <td>{{ number_format($sale->unit_price, 0) }}</td>
                                <td>{{ $sale->tax_rate }}</td>
                                <td>-</td>

                                <td>{{ number_format($coupon_set, 0) }}(クーポン適用価格)</td>
                                <td>{{ $sale->tax_rate }}</td>
                                <td>-</td>

                                
                                <td>{{ number_format($sale->shipping_fee, 0) }}</td>
                                <td>{{ $sale->tax_rate }}</td>
                                <td>-</td>
                                <td>{{ number_format($sale->shipping_fee, 0) }}</td>
                                <td>{{ number_format($sale->shipping_fee*$sale->quantity, 0) }}</td>

                                <td>{{number_format(($coupon_set + $sale->unit_price * ($sale->quantity - 1) + ($shipFee * $sale->quantity)), 0) }}</td>
                            @else    
                                <td>{{ number_format($sale->unit_price, 0) }}</td>
                                <td>{{ $sale->tax_rate }}</td>
                                <td>-</td>
                                <td>クーポン適用なし</td>
                                <td>---</td>
                                <td>---</td>

                                <td>{{ number_format($sale->shipping_fee, 0) }}</td>
                                <td>{{ $sale->tax_rate }}</td>
                                <td>-</td>
                                <td>{{ number_format($sale->shipping_fee, 0) }}</td>
                                <td>{{ number_format($sale->shipping_fee*$sale->quantity, 0) }}</td>
                                <td>{{ number_format(($sale->unit_price + $sale->shipping_fee)*$sale->quantity, 0) }}</td>
                                                           
                            @endif

                            <td>{{ $fee }}</td>
                            <td>{{ $fee_tax  }}</td>
                            <td>{{ $sale->tax_category }}</td>
                        </tr>
                    @endif    
                @empty
                    <tr>
                        <td colspan="12" class="text-center">データがありません</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>    


</div>
@endsection
