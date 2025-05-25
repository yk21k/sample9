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

	<h2> Your Cart </h2>
	<table class="table">
		<thead>
			<tr>
				<th>Photo</th>
				<th>Name</th>
				<th>Price</th>
				<th>Quantity</th>
				<th>Total</th>
				<th>Action</th>
				<th>Shop Name</th>
			</tr>
		</thead>

		<tbody>
		@foreach($cartItems as $item)
			
				<tr>
					<td>
						@if($item->associatedModel->shipping_fee)
							<img style="width: 96px; height: 65px;" class="card-img-top" src="{{ asset( 'storage/'.$item->associatedModel->cover_img1 ) }}" alt="Card image cap">
						@else
							<img style="width: 96px; height: 65px;" class="card-img-top" src="{{ asset( 'storage/'.$item->associatedModel->cover_img ) }}" alt="Card image cap">

						@endif
					</td>

					<td>{{ $item->name }}</td>
					
					<td>
						@php
							
							$shop_no = App\Models\Product::where('id', $item->id)->first();
							$priceTest = App\Models\Campaign::where('shop_id', $shop_no->shop_id)->first();
							$priceTest2 = App\Models\ShopCoupon::where('shop_id', $shop_no->shop_id)->first();

						@endphp
						@if($priceTest)
							@php
								if((int)$item->discounted_price > $item->finalPrice){
									$lowestPrice = $item->finalPrice;	
								}else{
									$lowestPrice = $item->discounted_price;
								}
							@endphp
							@if($lowestPrice = $item->finalPrice)

								¥{{ number_format($item->finalPrice) }}

							@elseif($lowestPrice = $item->discounted_price)

								¥{{ $item->discounted_price}}

							@else
								
								通常価格：¥{{$item->price}} 	

							@endif
						@elseif($priceTest2)
							@php
								if((int)$item->discounted_price > $item->finalPrice){
									$lowestPrice = $item->finalPrice;	
								}else{
									$lowestPrice = $item->discounted_price;
								}
							@endphp
							@if($lowestPrice = $item->finalPrice)

								¥{{ number_format($item->finalPrice) }}

							@elseif($lowestPrice = $item->discounted_price)

								¥{{ $item->discounted_price}}

							@else
								
								通常価格：¥{{$item->price}} 	

							@endif
						@else
							通常価格：¥{{$item->price}} 	
								
						@endif	
					</td>



					@if($item->associatedModel->shipping_fee)
						<td>Auction</td>
					@else
						<td>
							<form action="{{ route('cart.update', $item->id) }}">

								<input name="quantity" type="number" value="{{ $item->quantity }}" >
								<input type="submit" value="save">

							</form>	
						</td>
						
					@endif				
					<td>
						<a href="{{ route('cart.destroy', $item->id) }}">Delete</a>
					</td>
					<td>
						<a href="{{ route('shops.overview', $item->associatedModel->shop->id) }}">{{ $item->associatedModel->shop->name }}</a>	
					</td>

				</tr>
			<br>	

		@endforeach
				

		</tbody>
	</table>

	<div class="coupon">
		<form action="{{ route('cart.coupon') }}" method="get">
			<input class="input-text" type="text" id="coupon_code" name="coupon_code" value="" placeholder="Coupon code" required>
			<input class="button" name="apply_coupon" value="Apply coupon" type="submit">
		</form>
	</div>
	<br>
	<br>
	<div class="shopcoupon">
		<form action="{{ route('cart.shopcoupon') }}" method="get">
			<input class="input-text" type="text" id="shopcoupon_code" name="code" value="" placeholder="Shop Original Coupon code" required>
			<input class="button" name="apply_coupon" value="Apply coupon" type="submit">
		</form>
	</div>


	
	<button class="btn btn-danger button modalOpen" >Confirm payment details</button>
	
	  <div class="modal">
	    <div class="modal-inner">
	    <div class="modal-content">

	      <div class="modal-header">
	      	<div>
	      		<h2>Please confirm </h2>
	      		<div id="modalClose" class="modalClose">
			      close
			    </div>
	        	<h3>Order cancellations cannot be made on this website, so if the item you received does not match your order (type, quantity, etc.), please contact the individual seller. Please note that this website does not guarantee returns or refunds after an inquiry.

				If you agree, please check the checkbox below. You will be able to enter your shipping address and move to the payment page only after checking the checkbox.</h1>
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
	@php
	    $originalTotal = \Cart::session(auth()->id())->getSubTotalWithoutConditions();
	    $discountAmount = floor($originalTotal) - floor($total);
	    $discountPercent = $originalTotal > 0 ? round(($discountAmount / $originalTotal) * 100) : 0;

	    // 最も早く終了するキャンペーンを取得（カート内で割引があった場合）
	    $cartCampaigns = $cartItems->pluck('campaign')->filter()->unique('id');
	    $endingSoon = $cartCampaigns->sortBy('end_date')->first();
	    $remainingHours = null;

	    if ($endingSoon) {
	        $remainingHours = now()->diffInHours(\Carbon\Carbon::parse($endingSoon->end_date), false);
	    }
	@endphp

	    <h3 style="color: #b0c4de;">ご注文金額

		    <div class="price-line">
		        通常合計:
		        <p class="original-price">
		        	&nbsp;¥{{ ceil($originalTotal) }}
		        </p>
		         → 
		         <p class="discounted-price">
		         　割引適用後合計:　¥{{ ceil($total) }}
		     	</p>
		     	@if($discountPercent > 0)
	                <p class="discount-badge">-{{ $discountPercent }}% OFF</p>
	            @endif
		    </div>

		    @if($discountAmount > 0)
		        <div class="save-note">
		            🎉 ¥{{ round($discountAmount) }} お得になりました！
		        </div>
		    @endif

		    @if(!is_null($remainingHours))
		        <div class="campaign-end">
		            ⏳ カート内の商品のキャンペーンは、 {{ $remainingHours }} 時間で終了するものがあります
		        </div>
		    @endif

		    <div style="font-size: 0.75em; color: #b0c4de; margin-top: 0.6em;">
		        ※キャンペーン割引が自動で適用されています。
		    </div>
		</h3>
	@if(session('message'))
		<div>
			{{ session('message') }}
		</div>
	@endif
	
	<button class="btn btn-primary" id="submitButton" onclick="location.href='{{ route('cart.checkout') }}' " role="button">Proceed to Checkout</button>
	
	
@endsection