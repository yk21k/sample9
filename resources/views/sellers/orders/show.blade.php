@extends('layouts.seller')


@section('content')
<h3>Order Summary</h3>



@php
    use Carbon\Carbon;
    use App\Models\Campaign;
    use App\Models\Shop;
    use App\Models\ShopCoupon;

    $today = Carbon::today();
    $originalTotal = 0;
    $finalTotal = 0;
@endphp

<table class="table">
    <thead>
        <tr>
            <th>商品名</th>
            <th>数量</th>
            <th>手数料対象価格</th>
            <th>配送料</th>
            <th>手数料</th>
            <th>通常価格（配送料込）</th>
            <th>キャンペーン価格</th>
            <th>クーポン価格</th>
            <th>最終価格</th>
            <th>適用</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($items as $item)
            @php
                $quantity = $item->pivot->quantity;
                $basePrice = (float) $item->price;
                $shippingFee = (float) $item->shipping_fee;
                $originalPriceWithShipping = floor(($basePrice + $shippingFee)*1.1);
                $rate_and_fixed = App\Models\Commition::first();
                $feeRate = $rate_and_fixed->rate;
                $feeFixed = $rate_and_fixed->fixed;


                // --- 店舗とクーポンの取得 ---
                $shopSubOrder = App\Models\SubOrder::where('id', $item->pivot->sub_order_id)->first();
                $preShopId = App\Models\Shop::where('user_id', $shopSubOrder->seller_id)->first();
                $shopId = $preShopId->id;

                $preThisOrderCoupon = $shopSubOrder->coupon_code;

                $thisOrderCoupon = App\Models\ShopCoupon::where('code', $shopSubOrder->coupon_code)->first();

                $preThisOrderProductCoupon = App\Models\ShopCoupon::where('code', $shopSubOrder->coupon_code)->first();

                // --- キャンペーン価格 ---
                $campaign = Campaign::where('shop_id', $shopId)
                    ->where('status', 1)
                    ->where('start_date', '<=', $today)
                    ->where('end_date', '>=', $today)
                    ->orderByDesc('dicount_rate1')
                    ->first();

                $discountedPrice = $basePrice;
                if ($campaign) {
                    $discountedPrice = floor(($basePrice - ($basePrice * $campaign->dicount_rate1))*1.1);
                }
                $campaignPrice = floor($discountedPrice + $shippingFee*1.1);

                // --- クーポン価格 ---
                $couponPrice = $basePrice;
                if ($thisOrderCoupon && $thisOrderCoupon->product_id == $item->pivot->product_id) {
                    $couponPrice = floor(($basePrice + $thisOrderCoupon->value)*1.1);
                    $couponPrice = max($couponPrice, 0); // 負にならないように
                    
                    $couponPriceWithShipping = $couponPrice + $shippingFee*1.1;
                }else{
                    $couponPriceWithShipping = ($basePrice + $shippingFee)*1.1;
                }
                

                // --- 最終価格（1点だけ割引） ---
                $lowestUnitPrice = min($originalPriceWithShipping, $campaignPrice, $couponPriceWithShipping);
                $isDiscounted = $lowestUnitPrice < $originalPriceWithShipping;

                if ($isDiscounted && $quantity > 1) {
                    $finalLineTotal = $lowestUnitPrice + $originalPriceWithShipping * ($quantity - 1);
                } else {
                    $finalLineTotal = $lowestUnitPrice * $quantity;
                }

                $originalLineTotal = $originalPriceWithShipping * $quantity;
                $originalTotal += $originalLineTotal;
                $finalTotal += $finalLineTotal;

                // --- 適用判定 ---
                $appliedLabel = '適用なし';
                if ($campaign && $campaignPrice <= $couponPriceWithShipping) {
                    $appliedLabel = 'キャンペーン適用';
                } elseif ($thisOrderCoupon) {
                    $appliedLabel = 'クーポン適用';
                }
            @endphp

            <tr>
                @if($item->shop_id == $shopMane)
                    <td scope="row">
                        {{$item->name}}
                    </td>
                    <td>
                        {{$item->pivot->quantity}}
                    </td>
                    <td>
                        {{ $item->pivot->price*$item->pivot->quantity }}
                    </td>
                    <td>
                        {{ $item->shipping_fee }}
                    </td>
                    <td>
                        {{ (int) ($item->pivot->price*$item->pivot->quantity*$feeRate+$feeFixed) }}

                    </td>
                @endif

                <td>¥{{ number_format($originalPriceWithShipping) }}</td>
                <td>¥{{ number_format($campaignPrice) }}</td>
                <td>¥{{ number_format($couponPriceWithShipping) }}</td>
                <td>¥{{ number_format($lowestUnitPrice) }}</td>
                <td>
                    @if ($appliedLabel === 'キャンペーン適用')
                        <span class="label label-success">キャンペーン</span>
                    @elseif ($appliedLabel === 'クーポン適用')
                        <span class="label label-info">クーポン</span>
                    @else
                        <span class="label label-default">なし</span>
                    @endif
                </td>

            </tr>
            <tr>
                <td colspan="10" style="background-color: #f9f9f9;">
                    <strong>決済金額の内訳（{{ $item->name }}）:</strong><small>手数料は通常価格を対象にします。</small><br>

                    1個目（割引適用）: ¥{{ number_format($lowestUnitPrice) }}

                    @if ($quantity > 1)
                        <br>
                        2個目以降（{{ $quantity - 1 }}個 × 通常単価 ¥{{ number_format($originalPriceWithShipping) }}) = 
                        ¥{{ number_format($originalPriceWithShipping * ($quantity - 1)) }}
                    @endif
                    <br>
                    <strong>小計:</strong> ¥{{ number_format($finalLineTotal) }}
                </td>
            </tr>

        @endforeach
    </tbody>


</table>

<div class="text-right">
    <p><strong>通常価格合計:</strong> ¥{{ number_format($originalTotal) }}</p>
    <p><strong>決済金額合計:</strong> ¥{{ number_format($finalTotal) }}　<small>（クーポンまたはキャンペーン適用後）</small></p>
</div>


@endsection