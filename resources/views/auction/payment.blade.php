@extends('layouts.app')

@section('content')
<main class="payment-page">

    <div class="container">
        <h2>オークション決済</h2>
        @php
            use Carbon\Carbon;
            $now = Carbon::now();
            $endDate = Carbon::parse($auction->end);

        @endphp
        @if($now->greaterThan($endDate))
            <div class="auction-details">
                <p><strong>商品名:</strong> {{ $auction->name }}</p>
                <p>{{ $auction->description }}</p>
                <p><strong>即決価格:</strong> ¥{{ number_format($auction->spot_price+$auction->shipping_fee) }}</p>
                @if($bidAmount)
                    <p><strong>入札額:</strong> ¥{{ number_format($bidAmount+$auction->shipping_fee) }}</p>
                @else
                    <p><strong>現在の入札価格:</strong> ¥{{ number_format($auction->suggested_price+$auction->shipping_fee) }}</p>
                @endif
            </div>
        
            <form id="payment-form" action="{{ route('auction.charge', $auction->id) }}" method="POST">
                @csrf
                <div class="form-group">
                    <label for="card-element">クレジットカード情報</label>
                    <div id="card-element" class="form-control"></div>
                </div>
                <br>
                <button id="submit-button" class="btn btn-primary"><strong>入札価格:</strong> ¥{{ number_format(number_format($bidAmount+$auction->shipping_fee)) }}で支払う</button>
                <div id="card-errors" role="alert" class="text-danger mt-2"></div>
            </form><br>
            <a class="" href="{{ route('inquiries.create', ['id'=>$auction->shop_id]) }}"><h4>Contact Shop Manager</h4></a><br>
            <!-- オークションへ戻るボタン -->
            <a href="{{ route('home.auction.show', $auction->id) }}" class="btn btn-info">
                オークションへ戻る
            </a>
        @else   
            <div class="auction-details">
                <p><strong>商品名:</strong> {{ $auction->name }}</p>
                <p>{{ $auction->description }}</p>
                <p><strong>即決価格:</strong> ¥{{ number_format($auction->spot_price+$auction->shipping_fee) }}</p>

            </div>
        
            <form id="payment-form" action="{{ route('auction.charge', $auction->id) }}" method="POST">
                @csrf
                <div class="form-group">
                    <label for="card-element">クレジットカード情報</label>
                    <div id="card-element" class="form-control"></div>
                </div>
                <br>
                <button id="submit-button" class="btn btn-primary"><strong>即決価格:</strong> ¥{{ number_format($auction->spot_price+$auction->shipping_fee) }}で支払う</button>
                <div id="card-errors" role="alert" class="text-danger mt-2"></div>
            </form><br>
            <a class="" href="{{ route('inquiries.create', ['id'=>$auction->shop_id]) }}"><h4>Contact Shop Manager</h4></a><br>
            <!-- オークションへ戻るボタン -->
            <a href="{{ route('home.auction.show', $auction->id) }}" class="btn btn-info">
                オークションへ戻る
            </a>
        @endif

    </div>
</main>

<script src="https://js.stripe.com/v3/"></script>
<script>
    const stripe = Stripe("{{ env('STRIPE_KEY') }}");
    const elements = stripe.elements();
    const card = elements.create('card',{
        hidePostalCode: true
    });
    card.mount('#card-element');

    const form = document.getElementById('payment-form');
    form.addEventListener('submit', async function(event) {
        event.preventDefault();
        const {error, paymentMethod} = await stripe.createPaymentMethod('card', card);
        if (error) {
            document.getElementById('card-errors').textContent = error.message;
        } else {
            const hiddenInput = document.createElement('input');
            hiddenInput.type = 'hidden';
            hiddenInput.name = 'payment_method';
            hiddenInput.value = paymentMethod.id;
            form.appendChild(hiddenInput);
            form.submit();
        }
    });
</script>
@endsection
