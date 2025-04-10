@extends('layouts.app')

@section('content')

	<h2> Your Cart </h2>



	<table class="table">
		<thead>
			<tr>
				<th>Photo</th>
				<th>Name</th>
				<th>Price</th>
				<th>Quantity</th>
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
						$ {{ \Cart::session(auth()->id())->get($item->id)->getPriceSum() }}
						aa{{ \Cart::session(auth()->id())->getTotal() }}
						auction aa{{ $item->getPriceWithConditions() }}
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

	<h3>
		Total Price : $ {{ \Cart::session(auth()->id())->getTotal() }}
	</h3><br>
	@if(session('message'))
		<div>
			{{ session('message') }}
		</div>
	@endif
	
	<button class="btn btn-primary" id="submitButton" onclick="location.href='{{ route('cart.checkout') }}' " role="button">Proceed to Checkout</button>
	
	



@endsection