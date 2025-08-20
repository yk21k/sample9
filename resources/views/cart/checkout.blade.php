@extends('layouts.app')

@section('content')

<head>
   <meta name="csrf-token" content="{{ csrf_token() }}">
</head>   
<div class="container mt-5">
    <h2 class="text-center mb-4">Checkout</h2>
    @if(is_null($deliveryAddresses))
        <p class="text-center mb-4">
            <strong>ご安心ください。まだ決済されていません。</strong><br>配送先住所が表示されていない場合は、アカウントページから登録してください。<br>
            変更時は、アカウントページから変更してください。<br>
            <a href="{{ route('account.account', ['id'=>Auth::user()->id]) }}">配送先登録へ</a>
        </p><br>
    @else
        <h5 class="mb-3">配送先を選択：</h5>
        @foreach($setDeliPlaces as $index => $setDeliPlace)
            <div class="form-check">
                <input class="form-check-input" type="radio" name="selected_address" value="saved-{{ $index }}" id="saved-{{ $index }}" @if($index === 0) checked @endif>
                <label class="form-check-label" for="saved-{{ $index }}">
                    {{ $setDeliPlace->shipping_fullname }}（〒{{ $setDeliPlace->shipping_zipcode }} {{ $setDeliPlace->shipping_address }}）
                </label>
            </div>
        @endforeach

        <div class="form-check mt-2">
            <input class="form-check-input" type="radio" name="selected_address" value="new" id="new">
            <label class="form-check-label" for="new">前回の配送先に送付</label>
        </div>

        <form id="payment-form" action="{{ route('orders.store') }}" method="POST" class="h-adr shadow-lg p-4 rounded-lg">
        @csrf
            <input type="hidden" name="amount" value="{{ $cartTotal }}">
            <input type="hidden" class="p-country-name" value="Japan">

            <div class="form-group">
                <label>Full Name</label>
                <input type="text" name="shipping_fullname" id="shipping_fullname" class="form-control" required readonly>
            </div>

            <div class="form-group">
                <label>Postal Code</label>
                <input type="text" name="shipping_zipcode" id="shipping_zipcode" class="form-control p-postal-code" maxlength="8" required readonly>
            </div>

            <div class="form-group">
                <label>State</label>
                <input type="text" name="shipping_state" id="shipping_state" class="form-control p-region" required readonly>
            </div>

            <div class="form-group">
                <label>City</label>
                <input type="text" name="shipping_city" id="shipping_city" class="form-control p-locality" required readonly>
            </div>

            <div class="form-group">
                <label>Full Address</label>
                <input type="text" name="shipping_address" id="shipping_address" class="form-control p-street-address p-extended-address" required readonly>
            </div>

            <div class="form-group">
                <label>Mobile</label>
                <input type="text" name="shipping_phone" id="shipping_phone" class="form-control" required readonly>
            </div>

            <!-- Stripeなど -->
            <div class="form-check mb-4">
                <label class="form-check-label">
                    <input type="hidden" class="form-check-input" name="pay" value="stripe" readonly>
                    <!-- Hidden Stripe Payment Option -->
                </label>
            </div>

            <div class="form-group mb-4">
                <label for="card-element" class="font-weight-bold">Card Information</label>
                <div id="card-element" class="shadow-sm p-3 border rounded bg-white"></div>
            </div>

            <button id="submit" type="button" class="btn btn-success btn-lg w-48">
                <span id="submit-text">Pay Now</span>
                <span id="submit-loader" class="d-none spinner-border spinner-border-sm"></span>
            </button>

            <div id="payment-message" class="mt-3 text-center"></div>

        </form>

    @endif


</div>

<script src="https://js.stripe.com/v3/"></script>


<script>
    document.addEventListener('DOMContentLoaded', function () {
        const savedAddresses = @json($setDeliPlaces);
        const newAddress = @json($firstDelis);

        const fields = {
            fullname: document.getElementById('shipping_fullname'),
            zipcode: document.getElementById('shipping_zipcode'),
            state: document.getElementById('shipping_state'),
            city: document.getElementById('shipping_city'),
            address: document.getElementById('shipping_address'),
            phone: document.getElementById('shipping_phone')
        };

        const radios = document.querySelectorAll('input[name="selected_address"]');
        radios.forEach(radio => {
            radio.addEventListener('change', function () {
                if (this.value.startsWith('saved')) {
                    const index = this.value.split('-')[1];
                    const addr = savedAddresses[index];
                    fillFields(addr);
                } else {
                    fillFields(newAddress);
                }
            });
        });

        function fillFields(data) {
            fields.fullname.value = data?.shipping_fullname ?? '';
            fields.zipcode.value = data?.shipping_zipcode ?? '';
            fields.state.value = data?.shipping_state ?? '';
            fields.city.value = data?.shipping_city ?? '';
            fields.address.value = data?.shipping_address ?? '';
            fields.phone.value = data?.shipping_phone ?? '';
        }

        // 初期状態に1番目の保存済み配送先をセット
        fillFields(savedAddresses[0]);
    });
</script>


<script>
    const stripe = Stripe(@json(config('services.stripe.key')));
    const elements = stripe.elements();
    const card = elements.create('card', { hidePostalCode: true });
    card.mount('#card-element');

    const submitBtn = document.getElementById('submit');
    const text = document.getElementById('submit-text');
    const loader = document.getElementById('submit-loader');
    const form = document.getElementById('payment-form');
    const messageBox = document.getElementById('payment-message');

    submitBtn.addEventListener('click', async function () {
        // ボタン無効化・ローディング表示
        submitBtn.disabled = true;
        text.classList.add('d-none');
        loader.classList.remove('d-none');
        messageBox.textContent = '';
        messageBox.className = '';

        // 郵便番号チェック
        const postalCode = document.querySelector('input[name="shipping_zipcode"]').value;
        if (!/^\d{7}$/.test(postalCode)) {
            showError("郵便番号は7桁の数字でなければなりません。");
            return;
        }

        const customerName = document.querySelector('input[name="shipping_fullname"]').value;

        try {
            const paymentMethodResult = await stripe.createPaymentMethod({
                type: 'card',
                card: card,
                billing_details: {
                    name: customerName,
                    address: {
                        postal_code: postalCode
                    }
                }
            });

            if (paymentMethodResult.error) {
                showError(paymentMethodResult.error.message);
                return;
            }

            const paymentMethodId = paymentMethodResult.paymentMethod.id;
            const amount = document.querySelector('input[name="amount"]').value;

            const paymentIntentRes = await fetch('/create-payment-intent', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    amount: amount,
                    payment_method: paymentMethodId
                })
            });

            const paymentIntentData = await paymentIntentRes.json();

            if (!paymentIntentData.clientSecret) {
                showError("決済の準備に失敗しました。");
                return;
            }

            const confirmResult = await stripe.confirmCardPayment(paymentIntentData.clientSecret, {
                payment_method: {
                    card: card,
                    billing_details: {
                        name: customerName,
                        address: {
                            postal_code: postalCode
                        }
                    }
                }
            });

            if (confirmResult.error) {
                showError(confirmResult.error.message);
                return;
            }

            if (confirmResult.paymentIntent.status === 'succeeded') {
                messageBox.textContent = "決済が成功しました！注文を確定しています...";
                messageBox.classList.add('payment-success');


                const paymentMethodId = confirmResult.paymentIntent.payment_method;
                console.log("✅ paymentMethodId:", paymentMethodId); // ← 絶対出力する

                const payload = {
                    shipping_fullname: document.querySelector('input[name="shipping_fullname"]').value,
                    shipping_state: document.querySelector('input[name="shipping_state"]').value,
                    shipping_city: document.querySelector('input[name="shipping_city"]').value,
                    shipping_address: document.querySelector('input[name="shipping_address"]').value,
                    shipping_phone: document.querySelector('input[name="shipping_phone"]').value,
                    shipping_zipcode: document.querySelector('input[name="shipping_zipcode"]').value,
                    payment_method: paymentMethodId
                };

                console.log("📦 注文データ送信前:", payload); // ← ここが表示されないとダメ！

                // 注文情報送信
                const orderRes = await fetch('/orders', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify(payload)
                });
                const orderData = await orderRes.json();
                console.log("🎉 注文結果:", orderData);


                if (orderData.status === 'success') {
                    messageBox.textContent = "決済と注文が完了しました！リダイレクト中...";
                    setTimeout(() => window.location.href = "{{ route('payment.success') }}", 2000);
                } else {
                    showError(orderData.message || "注文処理に失敗しました。");
                }
            }

        } catch (error) {
            showError("エラーが発生しました: " + error.message);
        }
    });

    function showError(message) {
        const messageBox = document.getElementById('payment-message');
        messageBox.textContent = message;
        messageBox.className = 'payment-error';

        submitBtn.disabled = false;
        text.classList.remove('d-none');
        loader.classList.add('d-none');
    }
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
