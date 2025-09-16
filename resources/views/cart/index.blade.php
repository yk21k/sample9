@extends('layouts.app')

@section('content')
	<style>
	    .price-line {
	    	color: #b0c4de;
	        display: flex;
	        margin: 0.5em 0;
	    }

	    .original-price {
	        text-decoration: line-through;
	        color: #999;
	    }

	    .discounted-price {
	        color: #d32f2f;
	        font-weight: bold;
	    }

	    .discount-badge {
	        background: #ffdce0;
	        color: #c62828;
	        font-size: 0.8em;
	        font-weight: bold;
	        padding: 0.2em 0.6em;
	        border-radius: 12px;
	        display: inline-block;
	        margin-left: 0.5em;
	    }

	    .save-note {
	        color: #b0c4de;
	        font-size: 0.9em;
	        margin-top: 0.5em;
	    }

	    .campaign-end {
	        font-size: 0.8em;
	        color: #b0c4de;
	        margin-top: 0.4em;
	    }
	</style>
	@if(session('removed_message'))
    <div class="alert alert-warning">
        {{ session('removed_message') }}
    </div>
	@endif
	<h2> Your Cart </h2>

	@php
	    $totalAll = 0;
	@endphp

	<table class="table table-bordered">
	    <thead>
	        <tr>
	            <th>画像</th>
	            <th>商品名</th>
	            <th>価格</th>
	            <th>数量</th>
	            <th>小計</th>
	            <th>操作</th>
	            <th>ショップ</th>
	        </tr>
	    </thead>
	    
        @foreach ($cartItems as $item)
			@php
			    $tax_rate = App\Models\TaxRate::current()?->rate ?? 0;
			    $isTaxable = !empty($item->associatedModel->shop->invoice_number);

			    $shippingFee = (float) ($item->associatedModel->shipping_fee ?? 0);

			    if ($isTaxable) {
			        // 税込
			        $shippingFeeTaxed = floor($shippingFee * (1 + $tax_rate));
			        $originalPriceEx = (float) $item->price + $shippingFee; // 税抜
			        $originalPrice   = (float) $item->price * (1 + $tax_rate) + $shippingFeeTaxed; // 税込

			        $finalPrice = isset($item->final_price)
			            ? floor($item->final_price * (1 + $tax_rate) + $shippingFeeTaxed)
			            : $originalPrice;

			        $discountedPrice = isset($item->discounted_price)
			            ? floor($item->discounted_price * (1 + $tax_rate) + $shippingFeeTaxed)
			            : $originalPrice;

			        // 消費税額（1商品分）
			        $taxAmount = $originalPrice - $originalPriceEx;
			    } else {
			        // 免税
			        $originalPrice   = (float) $item->price + $shippingFee;
			        $originalPriceEx = $originalPrice; // 免税なので税込＝税抜
			        $finalPrice = isset($item->final_price)
			            ? floor($item->final_price + $shippingFee)
			            : $originalPrice;

			        $discountedPrice = isset($item->discounted_price)
			            ? floor($item->discounted_price + $shippingFee)
			            : $originalPrice;

			        $taxAmount = 0; // 免税事業者は税額なし
			    }

			    $lowestPrice   = min($finalPrice, $discountedPrice);
			    $isDiscounted  = $lowestPrice < $originalPrice;
			    $quantity      = $item->quantity;

			    if ($quantity > 1 && $isDiscounted) {
			        $totalPrice = $lowestPrice + $originalPrice * ($quantity - 1);
			    } else {
			        $totalPrice = $lowestPrice * $quantity;
			    }

			    $totalAll += $totalPrice;
			@endphp
            <tbody>
	            <tr>
	                <td>
	                    <img style="width: 96px; height: 65px;" class="card-img-top"
	                        src="{{ asset('storage/' . $item->associatedModel->cover_img) }}" alt="商品画像">
	                </td>
                    @if($item->associatedModel->shop->invoice_number)
	                	<td>{{ $item->name }} <span class="badge bg-danger ms-2">課税事業者</span></td>
	                @else	
	                	<td>{{ $item->name }} <span class="badge bg-success ms-2">免税事業者</span></td>
	                @endif	
	                <td>
					    ¥{{ number_format($lowestPrice) }}<br>
					    <small class="text-muted">税抜: ¥{{ number_format($originalPriceEx) }}</small><br>
					    @if($isTaxable)
					        <small class="text-warning">消費税: ¥{{ number_format($taxAmount) }}</small><br>
					    @endif
					    <small class="text-muted">税込: ¥{{ number_format($originalPrice) }}</small>

					    @if ($isDiscounted && $quantity > 1)
					        <br><small class="text-danger">※割引価格は1点のみ</small>
					    @endif
					</td>
	                <td>
	                    <form action="{{ route('cart.update', $item->id) }}" method="GET">
	                        <input name="quantity" type="number" value="{{ $quantity }}" min="1" style="width: 60px;">
	                        <button type="submit" class="btn btn-sm btn-primary">更新</button>
	                    </form>
	                </td>
	                <td>¥{{ number_format($totalPrice) }}</td>
	                <td>
	                    <a href="{{ route('cart.destroy', $item->id) }}" class="btn btn-sm btn-danger">削除</a>
	                </td>
	                <td>
	                	@if($item->associatedModel->shop->invoice_number)
		                    <a href="{{ route('shops.overview', $item->associatedModel->shop->id) }}">
		                        {{ $item->associatedModel->shop->name }} <span class="badge bg-danger ms-2">課税事業者</span>
		                    </a>
	                    @else
		                    <a href="{{ route('shops.overview', $item->associatedModel->shop->id) }}">
		                        {{ $item->associatedModel->shop->name }} <span class="badge bg-success ms-2">免税事業者</span>
		                    </a>
	                    @endif
	                </td>
	            </tr>
           	</tbody>
		    
                   
        @endforeach
        <tfoot>
	        <tr>
	            <td colspan="4" class="text-right"><strong>合計金額：</strong></td>
	            <td colspan="3"><strong>¥{{ number_format($totalAll) }}</strong></td>
	        </tr>
		</tfoot>
	</table>

<!-- 	20250719休止中<div class="coupon">
		<form action="{{ route('cart.coupon') }}" method="get">
			<input class="input-text" type="text" id="coupon_code" name="coupon_code" value="" placeholder="Coupon code" required>
			<input class="button" name="apply_coupon" value="Apply coupon" type="submit">
		</form>
	</div>
	<br> -->
	<br>
	<div class="shopcoupon">
		<form action="{{ route('cart.shopcoupon') }}" method="get">
			<input class="input-text" type="text" id="shopcoupon_code" name="code" value="" placeholder="Shop Original Coupon code" required>
			<input class="button" name="apply_coupon" value="Apply coupon" type="submit">
		</form>
	</div>
	<br><br>

	
	<button class="btn btn-danger button modalOpen" >Confirm payment details</button>
	
	  <div class="modal">
	    <div class="modal-inner">
	    <div class="modal-content">

	      <div class="modal-header">
	      	<div>
	      		<h2>決済に進む前にご確認ください </h2>
	      		<div id="modalClose" class="modalClose">
			      close
			    </div>
	        	<h3>当サイトではご注文のキャンセルは承っておりませんので、万が一、お届けした商品がご注文内容（種類、数量など）と異なる場合は、各出品者へお問い合わせください。なお、お問い合わせ後の返品・返金は保証いたしかねますのでご了承ください。ご同意いただける場合は、以下のチェックボックスにチェックを入れてください。チェックを入れた後のみ、配送先住所の入力やお支払いページへ移動できます。</h3>
	      	</div>
	        			
	      </div>

	      
	      <div class="modal-body">
	      	
	        <br>

	        <div class="contactAgree">
			  <label><input type="checkbox" name="agree" value="agreement"> Agree </label>
			</div>
	        <p></p>
	        
	      </div>

	    </div>
	    </div>
	 </div>
　　<div class="buffer"></div>
	<br>
	@php
	    $originalTotal = \Cart::session(auth()->id())->getSubTotalWithoutConditions();
        $tax_rate = App\Models\TaxRate::current()?->rate;

	    $shippingTotal = $cartItems->sum(function ($item) use ($tax_rate){
		    $shippingFee = (float) ($item->associatedModel->shipping_fee*($tax_rate+1) ?? 0);
		    return $shippingFee * $item->quantity;
		});

	    $originalTotalWithShipping = $originalTotal + $shippingTotal + $originalTotal*$tax_rate;

	    

	    $discountAmount = floor($originalTotalWithShipping) - floor($totalAll);
	    $discountPercent = $originalTotal > 0 ? round(($discountAmount / $originalTotal) * 100) : 0;

	    $cartCampaigns = $cartItems->pluck('campaign')->filter()->unique('id');
	    $endingSoon = $cartCampaigns->sortBy('end_date')->first();
	    $remainingHours = $endingSoon ? now()->diffInHours(\Carbon\Carbon::parse($endingSoon->end_date), false) : null;

	    session(['total_and_shipping' => $totalAll]); 
	    Log::info('total_and_shipping: ' . session('total_and_shipping')); 
	@endphp


	<h3 style="color: #b0c4de;">ご注文金額

	    <div class="price-line">

	    	@if(ceil($totalAll)===ceil($originalTotalWithShipping))
	    		合計:
				<p class="text-body fw-bold">
				    &nbsp;¥{{ number_format(ceil($originalTotalWithShipping)) }}
				</p>
	    	@else
	    		通常合計:
	    		<p class="original-price">
	            	&nbsp;¥{{ ceil($originalTotalWithShipping) }}
		        </p>
		        →
		        <p class="discounted-price">
		            割引適用後合計:　¥{{ number_format($totalAll) }}
		        </p>
	    	@endif
	    </div>
	    <br>
	    @if($discountAmount > 0)
	        <div class="save-note" style="color:tomato;">
	            🎉 ¥{{ ceil($originalTotalWithShipping - $totalAll)  }} お得になりました！
	        </div>
	    @endif

	    @if(!is_null($remainingHours))
	        <div class="campaign-end">
	            ⏳ カート内の商品のキャンペーンは、{{ $remainingHours }} 時間で終了するものがあります
	        </div>
	    @endif

	    <div style="font-size: 0.75em; color: #b0c4de; margin-top: 0.6em;">
	        ※キャンペーン割引適用時は割引が自動で適用されます。
	    </div>
	</h3>



	@if(session('message'))
		<div>
			{{ session('message') }}
		</div>
	@endif

	<button class="btn btn-primary" id="submitButton" onclick="location.href='{{ route('cart.checkout') }}' " role="button">Proceed to Checkout</button>
		

		<script>
	    const totalAmount = {{ session('total_and_shipping', 0) }};  // または $totalAll
	</script>
	<script>
		console.log("送信するtotal金額", totalAmount); // ← ここを確認
		fetch('/create-payment-intent', {
		  method: 'POST',
		  headers: {
		    'Content-Type': 'application/json',
		    'X-CSRF-TOKEN': '{{ csrf_token() }}'
		  },
		  body: JSON.stringify({ total: totalAmount })
		});


	</script>

	
@endsection