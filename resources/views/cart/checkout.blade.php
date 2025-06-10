@extends('layouts.app')

@section('content')
    <head>
	   <meta name="csrf-token" content="{{ csrf_token() }}">
    </head>   
	<div class="container mt-5">
		<h2 class="text-center mb-4">Checkout</h2>
		@if(!empty($deliveryAddresses))
			<p class="text-center">If your shipping address is not displayed, please register from the account page.</p>
		@endif

		@foreach($setDeliPlaces as $setDeliPlace)
		<form id="payment-form" action="{{ route('orders.store') }}" method="post" class="h-adr shadow-lg p-4 rounded-lg ">
		    @csrf
		    <h4 class="text-center text-primary mb-3">⭐️ Please enter your postal code within Japan to proceed with your purchase.</h4>
            <input type="hidden" name="amount" value="{{ $cartTotal }}">
		    <div class="form-group">
		        <label for="shipping_fullname" class="font-weight-bold">Full Name</label>
		        <input type="text" name="shipping_fullname" class="form-control shadow-sm" id="outputBox1" 
		        	@if(!empty($setDeliPlace)) value="{{ $setDeliPlace->shipping_fullname }}" @endif>
		    </div>
		    <span class="p-country-name" style="display:none;">Japan</span>
		    <div class="form-group">
		        <label for="shipping_zipcode" class="font-weight-bold">Postal Code</label>
		        <input type="text" class="form-control shadow-sm p-postal-code" name="shipping_zipcode" id="outputBox2" 
		        	@if(!empty($setDeliPlace)) value="{{ $setDeliPlace->shipping_zipcode }}" @endif maxlength="8" required>
		    </div>

		    <div class="form-group">
		        <label for="shipping_state" class="font-weight-bold">State</label>
		        <input type="text" name="shipping_state" class="form-control shadow-sm p-region" id="outputBox3" 
		        	@if(!empty($setDeliPlace)) value="{{ $setDeliPlace->shipping_state }}" @endif readonly>
		    </div>

		    <div class="form-group">
		        <label for="shipping_city" class="font-weight-bold">City</label>
		        <input type="text" name="shipping_city" class="form-control shadow-sm  p-locality" id="outputBox4" 
		        	@if(!empty($setDeliPlace)) value="{{ $setDeliPlace->shipping_city }}" @endif readonly>
		    </div>

		    <div class="form-group">
		        <label for="shipping_address" class="font-weight-bold">Full Address</label>
		        <input type="text" name="shipping_address" class="form-control shadow-sm p-street-address p-extended-address" id="outputBox5" 
		        	@if(!empty($setDeliPlace)) value="{{ $setDeliPlace->shipping_address }}" @endif>
		    </div>

		    <div class="form-group">
		        <label for="shipping_phone" class="font-weight-bold">Mobile</label>
		        <input type="text" name="shipping_phone" class="form-control shadow-sm" id="outputBox6" 
		        	@if(!empty($setDeliPlace)) value="{{ $setDeliPlace->shipping_phone }}" @endif>
		    </div>

		    <div class="form-check mb-4">
		        <label class="form-check-label">
		        	<input type="hidden" class="form-check-input" name="pay" value="stripe">
		            <!-- Hidden Stripe Payment Option -->
		        </label>
		    </div>

		    <div class="form-group mb-4">
		        <label for="card-element" class="font-weight-bold">Card Information</label>
		        <div id="card-element" class="shadow-sm p-3 border rounded bg-white"></div>
		    </div>

		    <div class="d-flex justify-content-between">
		        <button type="submit" id="submit" class="btn btn-success btn-lg w-48">Pay Now</button>
		    </div>

		    <p id="payment-message" class="mt-3 text-center"></p>
		</form>

		
		@endforeach
	</div>

	<script src="https://js.stripe.com/v3/"></script>


    <script>
        // var stripe = Stripe('{{ env('STRIPE_KEY') }}');
        var stripe = Stripe(@json(config('services.stripe.key')));
        var elements = stripe.elements();
        var card = elements.create('card', {
            hidePostalCode: true // 郵便番号を表示
        });
        card.mount('#card-element');

        // フォーム送信処理
        var form = document.getElementById('payment-form');
        form.addEventListener('submit', function(event) {
            event.preventDefault(); // デフォルトのフォーム送信を防ぐ

            // 郵便番号バリデーション
            var postalCode = document.querySelector('input[name="shipping_zipcode"]').value;
            if (postalCode.length !== 7 || !/^\d{7}$/.test(postalCode)) {
                document.getElementById('payment-message').textContent = "郵便番号は7桁の数字でなければなりません。";
                document.getElementById('payment-message').classList.add('payment-error');
                return;
            }
            var customerName = document.querySelector('input[name="shipping_fullname"]').value;
            // 支払い方法作成
            stripe.createPaymentMethod({
                type: 'card',
                card: card,
                billing_details: {
                    name: customerName, // 👈 ここが重要
                    address: {
                        postal_code: postalCode
                    }

                }
            }).then(function(result) {
                if (result.error) {
                    // エラーメッセージの表示
                    console.log(result.error.message);
                    document.getElementById('payment-message').textContent = result.error.message;
                    document.getElementById('payment-message').classList.add('payment-error');
                } else {
                    // 作成したPaymentMethod IDをサーバーに送信
                    var paymentMethodId = result.paymentMethod.id;
                    var formData = new FormData(form);
                    formData.append('payment_method', paymentMethodId);

                    const amount = document.querySelector('input[name="amount"]').value;

                    fetch('/create-payment-intent', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({
                            amount: amount,
                            payment_method: paymentMethodId
                        })
                    })
                    .then(response => response.json())  
                    .then(data => {
                        if (data.clientSecret) {
                            stripe.confirmCardPayment(data.clientSecret, {
                                payment_method: {
                                    card: card,
                                    billing_details: {
                                        address: {
                                            postal_code: postalCode
                                        }
                                    }
                                }
                            }).then(function(result) {
                                if (result.error) {
                                    console.error(result.error.message);
                                    document.getElementById('payment-message').textContent = result.error.message;
                                    document.getElementById('payment-message').classList.add('payment-error');
                                } else if (result.paymentIntent && result.paymentIntent.status === 'succeeded') {
                                    document.getElementById('payment-message').textContent = "決済が成功しました！";
                                    document.getElementById('payment-message').classList.add('payment-success');
                                    setTimeout(function() {
                                        window.location.href = "{{ route('payment.success') }}";
                                    }, 3000);
                                    
                                    // 注文情報送信
                                    fetch('/orders', {
                                        method: 'POST',
                                        headers: {
                                            'Content-Type': 'application/json',
                                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                                        },
                                        body: JSON.stringify({
                                            shipping_fullname: document.querySelector('input[name="shipping_fullname"]').value,
                                            shipping_state: document.querySelector('input[name="shipping_state"]').value,
                                            shipping_city: document.querySelector('input[name="shipping_city"]').value,
                                            shipping_address: document.querySelector('input[name="shipping_address"]').value,
                                            shipping_phone: document.querySelector('input[name="shipping_phone"]').value,
                                            shipping_zipcode: document.querySelector('input[name="shipping_zipcode"]').value,
                                            payment_method: paymentMethodId
                                        })
                                    })
                                    .then(res => res.json())
                                    .then(data => {
                                        if (data.status === 'success') {
                                            document.getElementById('payment-message').textContent = "決済と注文が完了しました！";
                                            document.getElementById('payment-message').classList.add('payment-success');
                                            setTimeout(function () {
                                                window.location.href = "{{ route('payment.success') }}";
                                            }, 3000);
                                        } else {
                                            document.getElementById('payment-message').textContent = data.message || "注文作成に失敗しました";
                                            document.getElementById('payment-message').classList.add('payment-error');
                                        }
                                    })
                                    .catch(err => {
                                        console.error(err);
                                        document.getElementById('payment-message').textContent = "注文作成中にエラーが発生しました";
                                        document.getElementById('payment-message').classList.add('payment-error');
                                    });
                                }
                            });
                        } else {
                            document.getElementById('payment-message').textContent = "決済の準備に失敗しました";
                            document.getElementById('payment-message').classList.add('payment-error');
                        }
                    })
                    .catch(error => {
                        console.error('ネットワークエラー:', error);
                        document.getElementById('payment-message').textContent = "通信エラーが発生しました";
                        document.getElementById('payment-message').classList.add('payment-error');
                    });
                }
            });
        });
    </script>
    

    <style>
        .shadow-sm {
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
        }
        .border {
            border: 1px solid #e0e0e0;
        }
        .rounded {
            border-radius: 8px;
        }
        .payment-error {
            color: red;
        }
        .payment-success {
            color: green;
        }
        .btn-lg {
            font-size: 1.2rem;
            padding: 0.75rem 1.25rem;
        }
        .btn-success {
            background-color: #28a745;
            border-color: #28a745;
        }
        .btn-primary {
            background-color: #007bff;
            border-color: #007bff;
        }
        #payment-message {
            font-size: 1rem;
            font-weight: bold;
            color: #e0dede;
        }
        
        /* 変更されたスタイル */
        label {
            font-weight: bold;
            color: white; /* 黒字 */
        }
        label.white-label {
            color: white; /* 白抜き部分を#708090に変更 */
        }

        @media (max-width: 768px) {
            .container {
                padding: 15px;
            }
            .btn-lg {
                font-size: 1rem;
                padding: 0.5rem 1rem;
            }
        }
    </style>
@endsection
