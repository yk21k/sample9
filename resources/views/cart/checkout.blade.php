@extends('layouts.app')

@section('content')


	<h2> Checkout </h2>

	
	<h3>Shipping Information</h3>

	<form class="h-adr" action="{{ route('orders.store') }}" method="post">
	    @csrf


	    <div class="form-group">
	        <label for="">Full Name</label>
	        <input type="text" name="shipping_fullname" id="" class="form-control">
	    </div>

	    <div class="form-group">
	        <label for="location_1"> <h3>Location * </h3><small>Please enter the address after entering the postal code.</small></label><br>
	        <span class="p-country-name" style="display:none;">Japan</span>
	        <label for="post-code">Postal Code:</label>
	        <input type="text" class="form-control p-postal-code" name="shipping_zipcode" size="8" maxlength="8"><br>
	        
    	</div>

	    <div class="form-group">
	        <label for="">State</label>
	        <input type="text" name="shipping_state" id="" class="form-control p-region">
	    </div>

	    <div class="form-group">
	        <label for="">City</label>
	        <input type="text" name="shipping_city" id="" class="form-control p-locality">
	    </div>

	    <div class="form-group">
	        <label for="">Full Address</label>
	        <input type="text" name="shipping_address" id="" class="form-control p-street-address p-extended-address">
	    </div>

	    <div class="form-group">
	        <label for="">Mobile</label>
	        <input type="text" name="shipping_phone" id="" class="form-control">
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


@endsection