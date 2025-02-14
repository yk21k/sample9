@extends('layouts.app')

@section('content')
@if ($errors->any())
    <div class="alert alert-danger">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
<div class="wrapper">
	<div>
    <header>
        
	</header>
	</div>
</div>
<h2> Your Account </h2>
		<ul class="nav">
		  <li class="nav-item">
		    <h3><a class="nav-link link-secondary" href="#">Order History</a></h3>
		  </li>
		  <li class="nav-item">
		    <h3><a class="nav-link link-secondary" href="#">Shipping Address</a></h3>
		  </li>
		  <li class="nav-item">
		    <h3><a class="nav-link link-secondary" href="{{ route('shops.create') }}">Open Your Shop</a></h3>
		  </li>
		  <li class="nav-item">
		    <h3><a class="nav-link link-secondary" href="{{ route('account.inquiry') }}">Contact Us</a></h3>
		  </li>
		  <li class="nav-item">
		    <h3><a class="nav-link link-secondary" href="#wishlist">Wish list</a></h3>
		  </li>
		  <li class="nav-item">
		    <h3><a class="nav-link link-secondary" href="#payment-methods">-----</a></h3>
		  </li>
		  <li class="nav-item">
		    <h3><a class="nav-link link-secondary" href="#account-settings">Account Settings</a></h3>
		  </li>
		  <li class="nav-item">
		    <h3><a class="nav-link link-secondary" href="#support">Support</a></h3>
		  </li>
		</ul>
        
<div class="container">
    <main>
        <section id="order-history" class="section_accocnt">
            <h2>Order history</h2>
            <ul>
            	<h4>Search</h4>
            	<input id="myInput" type="date">&nbsp;&nbsp;&nbsp;<button type="button" class="btn btn-info" id="hide_button">Hide/Display</button><br><br>
            	<table class="table table-bordered table-striped" id="table_order1">
	            	<thead>	
		        	    <tr>
		        	    	<td>Delivery Status</td>
		        	    	<td>Shipping Company</td>
		        	    	<td>Invoice Number</td>
					        <td>Order Number</td>
					        <td>Purchase Date</td>
					        <td>Name</td>
					        <td>Zip-code</td>
					        <td>Address</td>
				        </tr>
			        </thead>
		        	@foreach($order_histories as $order_history)
		        	<tbody id="myTable">
		        		<tr>
		        			@if($order_history->status=="pending" && $order_history->payment_status=="")
		        				
		        				<td>*Under preparation</td>

		        			@elseif($order_history->status=="pending" && $order_history->payment_status=="accepted")
		        				
		        				<td>**Accepted</td>	

		        			@elseif($order_history->status=="pending" && $order_history->payment_status=="arranging delivery")
		        				
		        				<td>⭐　Arranging Delivery</td>

		        			@elseif($order_history->status=="processing" && $order_history->payment_status=="delivery arranged")

		        				<td>⭐⭐️　Delivery Arranged</td>

		        			@elseif($order_history->status=="completed")	
		        				<td>*Delivered*</td>
		        			@else
		        				<td>Details Unknown</td>
		        			@endif
		        			<td>{{ $order_history->shipping_company }}</td>
		        			<td>^^^^^^^</td>
		        			<td>{{ $order_history->order_id }}</td>
		        			<td>{{ $order_history->created_at }}</td>
		        			@foreach($shipping_names as $shipping_name)
		        			<td>{{ $shipping_name->shipping_fullname }}</td>
		        			<td>{{ $shipping_name->shipping_zipcode }}</td>
		        			<td>{{ $shipping_name->shipping_state }} {{ $shipping_name->shipping_city }} {{ $shipping_name->shipping_address }}</td>
		        			@endforeach
		        		</tr>
			        </tbody>
			        @endforeach
		    	</table>
                
                <br>
                
            </ul>
        </section>

        <section id="delivery-status" class="section">
            <h2>Delivery Status</h2>
            <ul>
                <li>商品名: XYZ スニーカー - ¥6,000</li>
                <li>商品名: ABC シャツ - ¥2,500</li>
                <!-- 他のお気に入り商品 -->
            </ul>
        </section>

        <section id="wishlist" class="section">
            <h2>お気に入り</h2>
            <ul>
                <li>商品名: XYZ スニーカー - ¥6,000</li>
                <li>商品名: ABC シャツ - ¥2,500</li>
                <!-- 他のお気に入り商品 -->
            </ul>
        </section>

        <section id="addresses" class="section">
            <h2>Shipping address</h2>
            @if($firstDelis)
            <p>Latest Address: 〒{{ $firstDelis->shipping_zipcode }}
            {{ $firstDelis->shipping_state }}
            {{ $firstDelis->shipping_city }}
            {{ $firstDelis->shipping_address }}</p>
            @foreach($savedDelis as $savedDeli)
            	<p>Other Addresses: 〒{{ $savedDeli->shipping_zipcode }} {{ $savedDeli->shipping_state }} {{ $savedDeli->shipping_city }} {{ $savedDeli->shipping_address }}</p>
            @endforeach
            @else
            <button type="button" class="btn btn-info" id="hide_button2">Hide/Display Register new address</button><br><br>
			<form class="h-adr" id="address1" action="{{route('account.addresses', Auth::user()->id)}}" method="post">@csrf

            	<h4>The Other Address</h4>
	            <div class="form-group">
			        <label for="">Full Name</label>
			        <input type="text" name="shipping_fullname" id="" class="form-control" required>
			    </div>

			    <div class="form-group">
			        <label for="location_1"> <h3>Location * </h3><small>⭐️Please enter the address after entering the postal code.</small></label><br>
			        <span class="p-country-name" style="display:none;">Japan</span>
			        <label for="post-code">Postal Code:</label>
			        <input type="text" class="form-control p-postal-code" name="shipping_zipcode" size="8" maxlength="8" required><br>
			        
		    	</div>

			    <div class="form-group">
			        <label for="">State</label>
			        <input type="text" name="shipping_state" id="" class="form-control p-region" readonly>
			    </div>

			    <div class="form-group">
			        <label for="">City</label>
			        <input type="text" name="shipping_city" id="" class="form-control p-locality" readonly>
			    </div>

			    <div class="form-group">
			        <label for="">Full Address</label>
			        <input type="text" name="shipping_address" id="" class="form-control p-street-address p-extended-address" required>
			    </div>

			    <div class="form-group">
			        <label for="">Mobile</label>
			        <input type="text" name="shipping_phone" id="" class="form-control" required>
			    </div>
		    	<button type="submit" class="btn btn-primary mt-3">save address</button>

            </form>
            @endif
        </section>

        <section id="payment-methods" class="section">
            <h2>支払い方法</h2>
            <p>クレジットカード: xxxx-xxxx-xxxx-1234</p>
            <p>PayPalアカウント: example@email.com</p>
            <button>支払い方法を追加</button>
        </section>

        <section id="account-settings" class="section">
            <h2>Account Settings</h2>
            <p><strong>Username: {{ Auth::user()->name }}</strong></p>
            <p><strong>Email: {{ Auth::user()->email }}</strong></p>
            <div id="account-settings" class="section_accocnt">
    		<h3>Enter it and click the Update button to change it.</h3>
				<form action="{{route('account.account', Auth::user()->id)}}" method="post">@csrf
					<p>Your Name:<input type="text" name="name" value="{{ ($profiles->name) }}"></p>
					<p>Email: <input type="text" name="email" value="{{ ($profiles->email) }}"></p>
					<p>Password: <input type="text" name="password" value=""></p>
					<p>Start: {{ ($profiles->created_at) }}</p>
					<p>Latest: {{ ($profiles->updated_at) }}</p>
			        <button class="btn btn-primary" type="submit">Update</button>
				</form>
			</div><br>
        </section>

        <section id="support" class="section">
            <h2>サポート</h2>
            <p>FAQ | お問い合わせフォーム | カスタマーサポート電話番号: 123-456-7890</p>
        </section>
    </main>
</div>	
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
<script>
$(document).ready(function(){
  $("#myInput").on("keyup", function() {
    var value = $(this).val().toLowerCase();
    $("#myTable tr").filter(function() {
      $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
    });
  });
});
</script>
<script>
$(function() {
    $("#hide_button").click(function() {
        $("#table_order1").slideToggle("");
    });
});

$(function() {
    $("#hide_button2").click(function() {
        $("#address1").slideToggle("");
    });
});
</script>

@endsection