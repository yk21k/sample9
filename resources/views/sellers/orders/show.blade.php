@extends('layouts.seller')

@section('content')
<h3>Order Summary</h3>

<table class="table table-bordered">
    <thead>
        @php
            $isTaxable = $suborder->is_taxable ?? false;  
        @endphp
        <tr>
            <th style="white-space: nowrap;">商品名</th>
            <th style="white-space: nowrap;">数量</th>
            <th style="white-space: nowrap;">通常価格合計</th>
            <th style="white-space: nowrap;">送料</th>
            <th style="white-space: nowrap;">手数料</th>
            <th style="white-space: nowrap;">キャンペーン価格</th>
            <th style="white-space: nowrap;">クーポン価格</th>
            <th style="white-space: nowrap;">適用価格</th>
            @if($isTaxable==1)
                <th style="white-space: nowrap;">消費税(適用価格)</th>
            @else
                <th style="white-space: nowrap;">非課税</th>
            @endif    
            <th style="white-space: nowrap;">適用</th>
        </tr>
    </thead>
    <tbody>
        @foreach($items as $item)
            @php
                $isTaxable = $suborder->is_taxable ?? false;
                $tax_rate = \App\Models\TaxRate::current()?->rate ?? 0;

                $quantity = $item->pivot->quantity;
                $unitPrice = $item->pivot->price;                // 割引前
                $discountedUnitPrice = $item->pivot->discounted_price; // 割引後
                $subtotal = $item->pivot->subtotal;             // 割引後
                $taxAmount = $item->pivot->tax_amount;         // 割引後税額
                $shippingFee = $item->shipping_fee;
                $feeRate = \App\Models\Commition::first()->rate ?? 0;
                $feeFixed = 0;

                // 割引前の合計
                $totalOriginal = $unitPrice * $quantity;
                // 送料合計
                $totalShipping = $shippingFee * $quantity;

                // 手数料は割引なしの価格で計算、送料は含まない
                $fee = (int)($totalOriginal * $feeRate + $feeFixed);

                // キャンペーン価格
                $campaignPrice = $item->pivot->campaign_id ? $discountedUnitPrice : $unitPrice;
                // クーポン価格
                $couponPrice = $item->pivot->coupon_id ? $discountedUnitPrice : $unitPrice;

                // まずデフォルト（= 通常価格）で初期化
                $displayCampaignPrice = $unitPrice;
                $displayCouponPrice   = $unitPrice;

                // キャンペーン適用（表示用）
                if($item->pivot->campaign_id) {
                    $campaign = \App\Models\Campaign::find($item->pivot->campaign_id);
                    if($campaign) {
                        $displayCampaignPrice = floor($unitPrice * (1 - $campaign->dicount_rate1));
                    }
                }

                // クーポン適用（表示用）
                if($item->pivot->coupon_id) {
                    $coupon = \App\Models\ShopCoupon::find($item->pivot->coupon_id);
                    if($coupon) {
                        $displayCouponPrice = max(0, $unitPrice + $coupon->value);
                    }
                }



                // 最終価格（割引後 + 送料）
                $finalLineTotal = $subtotal + $totalShipping;

                // 適用ラベル
                if($item->pivot->campaign_id) {
                    $appliedLabel = 'キャンペーン適用';
                } elseif($item->pivot->coupon_id) {
                    $appliedLabel = 'クーポン適用';
                } else {
                    $appliedLabel = 'なし';
                }
            @endphp
            @if($isTaxable==1)

                <tr>
                    <td>{{ $item->name }} <span class="badge bg-danger">課税業者</span></td>
                    <td>{{ $quantity }}</td>
                    <td>¥{{ number_format($totalOriginal) }}</td>
                    <td>¥{{ number_format($totalShipping) }}</td>
                    <td>¥{{ number_format($fee) }}</td>
                    <td>¥{{ number_format($displayCampaignPrice + $unitPrice * ($quantity -1)) }}</td>
                    <td>¥{{ number_format($displayCouponPrice + $unitPrice * ($quantity -1)) }}</td>

                    <td>¥{{ number_format($subtotal) }}</td>
                    <td>¥{{ number_format($subtotal * $tax_rate) }}</td>
                    <td>
                        @if(trim($appliedLabel) === 'キャンペーン適用')
                            <span class="badge bg-success">キャンペーン</span>
                        @elseif(trim($appliedLabel) === 'クーポン適用')
                            <span class="badge bg-info">クーポン</span>
                        @else
                            <span class="badge bg-secondary">なし</span>
                        @endif
                    </td>
                </tr>
                
                {{-- 商品行 --}}
                <tr>
                    <td>{{ $item->name }}</td>
                    <td>
                        ¥{{ number_format(($discountedUnitPrice + $unitPrice) * ($tax_rate + 1) + $shippingFee * $quantity * ($tax_rate + 1)) }}
                        <button class="toggle-details btn btn-sm btn-link" data-target="details-{{ $item->id }}">
                            詳細を表示
                        </button>
                    </td>
                </tr>

                {{-- 詳細行（折り畳み部分） --}}
                <tr id="details-{{ $item->id }}" class="details-row" style="display: none; background-color: #f9f9f9;">
                    <td colspan="9">
                        <strong>決済内訳（{{ $item->name }}）:</strong><br>
                        1個目（割引適用）: ¥{{ number_format($discountedUnitPrice) }}<br>
                        1個目（消費税）: ¥{{ number_format($discountedUnitPrice * $tax_rate) }}

                        @if($quantity > 1)
                            <br>2個目以降（{{ $quantity - 1 }}個 × ¥{{ number_format($unitPrice) }}) = 
                            ¥{{ number_format($unitPrice * ($quantity - 1)) }}
                            <br>2個目以降(消費税): ¥{{ number_format($unitPrice * ($quantity - 1) * $tax_rate) }}
                        @endif

                        <br><strong>小計:</strong> ¥{{ number_format(($discountedUnitPrice + $unitPrice) * ($tax_rate + 1)) }}
                        <br><strong>送料:</strong> ¥{{ number_format($totalShipping) }}
                        <br><strong>送料(消費税):</strong> ¥{{ number_format($totalShipping * $tax_rate) }}
                        <br><strong>最終合計:</strong> 
                        ¥{{ number_format(($discountedUnitPrice + $unitPrice) * ($tax_rate + 1) + $shippingFee * $quantity * ($tax_rate + 1)) }}

                        <br><p>通常価格合計:（キャンペーンやクーポン適用なし）
                            ¥{{ number_format(($unitPrice + $shippingFee) * $quantity * ($tax_rate + 1)) }}
                        </p>

                        <p><strong>決済金額合計（お客様からの入金）:</strong>
                            ¥{{ number_format(($discountedUnitPrice + $unitPrice) * ($tax_rate + 1) + $shippingFee * $quantity * ($tax_rate + 1)) }} 
                            <small>（クーポンまたはキャンペーン適用後）</small>
                        </p>

                        <p>
                            当サイトへの支払い(消費税込)：¥{{ number_format($fee) }} 
                        </p>
                        <p>
                            Shopへの入金：¥{{ number_format(($discountedUnitPrice + $unitPrice) * ($tax_rate + 1) + $shippingFee * $quantity * ($tax_rate + 1) - $fee) }} 
                        </p>
                    </td>
                </tr>

            @else
                <tr>
                               
                    <td>{{ $item->name }}</td>
                    <td>{{ $quantity }}</td>
                    <td>¥{{ number_format($totalOriginal) }}</td>
                    <td>¥{{ number_format($totalShipping) }}</td>
                    <td>¥{{ number_format($fee) }}</td>
                    <td>¥{{ number_format($displayCampaignPrice + $unitPrice * ($quantity -1)) }}</td>
                    <td>¥{{ number_format($displayCouponPrice + $unitPrice * ($quantity -1)) }}</td>

                    <td>¥{{ number_format($subtotal) }}</td>
                    <td>-</td>
                    <td>
                        @if(trim($appliedLabel) === 'キャンペーン適用')
                            <span class="badge bg-success">キャンペーン</span>
                        @elseif(trim($appliedLabel) === 'クーポン適用')
                            <span class="badge bg-info">クーポン</span>
                        @else
                            <span class="badge bg-secondary">なし</span>
                        @endif
                    </td>
                </tr>

                {{-- 商品行 --}}
                <tr>
                    <td>{{ $item->name }}</td>
                    <td>
                        ¥{{ number_format($finalLineTotal) }}
                        <button class="toggle-details btn btn-sm btn-link" data-target="details-{{ $item->id }}">
                            詳細を表示
                        </button>
                    </td>
                </tr>

                {{-- 折りたたみ用の詳細行 --}}
                <tr id="details-{{ $item->id }}" class="details-row" style="display: none; background-color: #f9f9f9;">
                    <td colspan="9">
                        <strong>決済内訳（{{ $item->name }}）:</strong><br>
                        1個目（割引適用）: ¥{{ number_format($discountedUnitPrice) }}
                        @if($quantity > 1)
                            <br>2個目以降（{{ $quantity - 1 }}個 × ¥{{ number_format($unitPrice) }}) = ¥{{ number_format($unitPrice * ($quantity - 1)) }}
                        @endif
                        <br><strong>小計:</strong> ¥{{ number_format($subtotal) }}
                        <br><strong>送料:</strong> ¥{{ number_format($totalShipping) }}
                        <br><strong>最終合計:</strong> ¥{{ number_format($finalLineTotal) }}
                        <br><p>通常価格合計:（キャンペーンやクーポン適用なし）
                            ¥{{ number_format(($unitPrice + $shippingFee) * $quantity) }}
                        </p>

                        <p><strong>決済金額合計（お客様からの入金）:</strong>
                            ¥{{ number_format($discountedUnitPrice + $shippingFee + ($unitPrice + $shippingFee) * ($quantity -1)) }} 
                            <small>（クーポンまたはキャンペーン適用後）</small>
                        </p>

                        <p>
                            当サイトへの支払い(消費税込)：¥{{ number_format($fee) }} 
                        </p>
                        <p>
                            Shopへの入金：¥{{ number_format(($discountedUnitPrice + $unitPrice) + $shippingFee * $quantity - $fee) }} 
                        </p>
                    </td>
                </tr>

            @endif    
        @endforeach
    </tbody>
</table>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const buttons = document.querySelectorAll('.toggle-details');

        buttons.forEach(function (btn) {
            btn.addEventListener('click', function () {
                const targetId = btn.getAttribute('data-target');
                const targetRow = document.getElementById(targetId);
                const isHidden = targetRow.style.display === 'none' || targetRow.style.display === '';

                // すべて閉じる
                document.querySelectorAll('.details-row').forEach(function (row) {
                    row.style.display = 'none';
                });
                document.querySelectorAll('.toggle-details').forEach(function (b) {
                    b.textContent = '詳細を表示';
                });

                // クリックしたやつだけ開く
                if (isHidden) {
                    targetRow.style.display = 'table-row';
                    btn.textContent = '詳細を隠す';
                }
            });
        });
    });
</script>

@endsection
