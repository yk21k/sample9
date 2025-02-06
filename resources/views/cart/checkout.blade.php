@extends('layouts.app')

@section('content')
	
	<div class="container">
		<h2> Checkout </h2>
		
			If your shipping address is not displayed, please register from the account page.

			@foreach($deliveryAddresses as $deliveryAddress)
				
				<form action="{{ route('cart.deli_place') }}" method="post">@csrf
					<input type="text" name="shipping_id" data-addressid="{{ $deliveryAddress->id}}"  value="{{ $deliveryAddress->id}}" style="display:none;">

				    <button type="submit" class="btn btn-primary mt-3" onclick="myFunction()"> Select </button>

					〒{{ $deliveryAddress->shipping_zipcode }} {{ $deliveryAddress->shipping_state }} {{ $deliveryAddress->shipping_city }} {{ $deliveryAddress->shipping_address }}

				</form>
					
			@endforeach
				
			@foreach($setDeliPlaces as $setDeliPlace)
			<form class="h-adr" action="{{route('orders.store')}}" method="post">@csrf

			    <h4>⭐️ To proceed with your purchase, please enter your postal code within Japan.</h4>
			    <div class="form-group">
			        <label for="">Full Name</label>
			        	<input type="text" name="shipping_fullname" class="form-control" id="outputBox1" @if(!empty($setDeliPlace)) value="{{ $setDeliPlace->shipping_fullname  }}" @endif>
			    </div>
			    <div class="form-group">
			        <label for="location_1"> <h3>Location * </h3><small>⭐️Please enter the address after entering the postal code.</small></label><br>
			        <span class="p-country-name" style="display:none;">Japan</span>
			        <label for="post-code">Postal Code:</label>
			        	
			        	<input type="text" class="form-control p-postal-code" name="shipping_zipcode" id="outputBox2" size="8" maxlength="8" @if(!empty($setDeliPlace)) value="{{ $setDeliPlace->shipping_zipcode  }}" @endif><br>
		    	</div>

			    <div class="form-group">
			        <label for="">State</label>
			        	
			        	<input type="text" name="shipping_state" id="outputBox3" class="form-control p-region" @if(!empty($setDeliPlace)) value="{{ $setDeliPlace->shipping_state  }}" @endif readonly>
			    </div>

			    <div class="form-group">
			        <label for="">City</label>
			        	
			        	<input type="text" name="shipping_city" id="outputBox4" class="form-control p-locality" @if(!empty($setDeliPlace)) value="{{ $setDeliPlace->shipping_city  }}" @endif readonly>
			    </div>

			    <div class="form-group">
			        <label for="">Full Address</label>
			        	
			        	<input type="text" name="shipping_address" class="form-control p-street-address p-extended-address" id="outputBox5" @if(!empty($setDeliPlace)) value="{{ $setDeliPlace->shipping_address  }}" @endif>
			    </div>
			    <div class="form-group">
			        <label for="">Mobile</label>
			        	
			        	<input type="text" name="shipping_phone" class="form-control p-street-address p-extended-address" id="outputBox6" @if(!empty($setDeliPlace)) value="{{ $setDeliPlace->shipping_phone  }}" @endif>
			    </div>

			    <h4>Payment option</h4>

			    <div class="form-check">
			        <label class="form-check-label">
			            <input type="radio" checked class="form-check-input" name="payment_method" id="" value="cash_on_delivery">
			            Cash on delivery
			        </label>
			    </div>

			    <div class="form-check">
			        <label class="form-check-label">
			            <input type="radio" class="form-check-input" name="payment_method" id="" value="paypal">
			            Paypal
			        </label>
			    </div>

			    <button type="submit" class="btn btn-primary mt-3">Place Order</button>

			</form>
			@endforeach
			
	</div>


@endsection