@if(auth()->user()===null)
	<h2>Page not found. Please check the URL and log in again if necessary.</h2>
@else

@extends('layouts.seller')


@section('content')

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shop Create Coupon</title>
    <!-- <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            display: flex;
            align-items: center;
            background-color: #f4f4f9;
        }
        .form-container-coupon {
            background-color: #4682b4;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            width: ;
        }
        .form-container-coupon h2 {
            color:#f0f8ff;
            text-align: center;
            margin-bottom: 20px;
        }
        .form-container-coupon label {
            color:#f0f8ff;
            font-size: 14px;
            margin-bottom: 5px;
            display: block;
        }
        .form-container-coupon input[type="text"],
        .form-container-coupon input[type="date"],
        .form-container-coupon input[type="number"] {
            width: 100%;
            padding: 8px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        .form-container-coupon button {
            width: 30%;
            padding: 10px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }
        .form-container-coupon button:hover {
            background-color: #45a049;
        }
    </style> -->
</head>
<body>
	@if (session('success'))
	    <div class="alert alert-success">
	        {{ session('success') }}
	    </div>
	@endif
    <div class="form-container-coupon">
        <h2>Shop Coupon Create</h2>
        <form action="{{ route('order.make_coupon') }}" method="POST"> @csrf

            <label for="expiry_date">Expiry date:</label>
            <input type="date" id="expiry_date" name="expiry_date" required>

            <label for="sheets">Sheets:</label>
            <input type="number" id="sheets" name="sheets" min="1" required>

            <label for="product">Product:</label>
            @php
                $product_shop_coupons = App\Models\Product::where('shop_id', auth()->user()->shop->id)->get()
            @endphp
            <select name="product_id">
                @foreach($product_shop_coupons as $pscoupon)
                    
                    <option name="product_id" value="{{ $pscoupon->id }}" id="product_id"  > {{ $pscoupon->name }}</option>


                @endforeach    
            </select> 

            <label for="value">Value:</label>
            <input type="text" id="value" name="value" min="-50%" required>

            <label for="description">Description:</label>
            <input type="text" id="description" name="description">

            <button type="submit">Submit</button>
        </form>
    </div>

</body>

@endsection

@endif