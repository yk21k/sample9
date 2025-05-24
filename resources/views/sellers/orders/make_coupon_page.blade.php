@if(auth()->user()===null)
	<h2>Page not found. Please check the URL and log in again if necessary.</h2>
@else

@extends('layouts.seller')


@section('content')

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shop Create Coupon</title>
    <style>
        .form-container-coupon {
            max-width: 500px;
            margin: 40px auto;
            background: #f9f9f9;
            padding: 30px 40px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            font-family: 'Arial', sans-serif;
        }

        .form-container-coupon h2 {
            margin-bottom: 20px;
            color: #333;
            text-align: center;
        }

        .form-container-coupon label {
            display: block;
            margin-top: 15px;
            font-weight: bold;
            color: #555;
        }

        .form-container-coupon input,
        .form-container-coupon select,
        .form-container-coupon button {
            width: 100%;
            padding: 10px;
            margin-top: 8px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 14px;
        }

        .form-container-coupon button {
            background-color: #007BFF;
            color: white;
            font-weight: bold;
            cursor: pointer;
            transition: background-color 0.3s;
            margin-top: 20px;
        }

        .form-container-coupon button:hover {
            background-color: #0056b3;
        }
    </style>
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

            <label for="expiry_date">有効期限:</label>
            <input type="date" id="expiry_date" name="expiry_date" required>

            <label for="sheets">枚数:</label>
            <input type="number" id="sheets" name="sheets" min="1" required>

            <label for="product_id">商品:</label>
            @php
                $product_shop_coupons = App\Models\Product::where('shop_id', auth()->user()->shop->id)->get()
            @endphp
            <select name="product_id" id="product_id">
                @foreach($product_shop_coupons as $pscoupon)
                    <option value="{{ $pscoupon->id }}">{{ $pscoupon->name }}</option>
                @endforeach    
            </select> 

            <label for="value">割引額（※半角ハイフン付き数字のみ）:</label>
            <input type="text" id="value" name="value" placeholder="例　-100 半角に変更してから入力ください" required>


            <label for="description">説明:</label>
            <input type="text" id="description" name="description">

            <button type="submit">作成する</button>
        </form>
    </div>

</body>

<script>
document.getElementById('value').addEventListener('input', function (e) {
    this.value = this.value.replace(/[^\d\-%]/g, ''); // 数字、-、%以外を除外
});
</script>

@endsection

@endif