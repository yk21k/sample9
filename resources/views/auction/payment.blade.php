@extends('layouts.app')

@section('content')
<main class="payment-page">
    <div class="container">
        <h2>決済情報</h2>

        <div class="auction-details">
            <h3>{{ $auction->name }}</h3>
            <p>{{ $auction->description }}</p>
            <p><strong>即決価格:</strong> ¥{{ number_format($auction->spot_price) }}</p>

            @if($bidAmount)
                <p><strong>入札額:</strong> ¥{{ number_format($bidAmount) }}</p>
            @else
                <p><strong>現在の入札価格:</strong> ¥{{ number_format($auction->suggested_price) }}</p>
            @endif
        </div>
        <!-- オークションへ戻るボタン -->
        <a href="{{ route('home.auction.show', $auction->id) }}" class="btn btn-primary">
            オークションへ戻る
        </a>
        <form action="{{ route('cart.add.auction', $auction->id) }}" method="POST">
            @csrf
            <input type="hidden" name="amount" value="{{ $bidAmount ?? $auction->suggested_price }}">
            <button type="submit" class="btn btn-success">決済を進める</button>
        </form>
        <a class="" href="{{ route('inquiries.create', ['id'=>$auction->shop_id]) }}"><h4>Contact Shop Manager</h4></a>
        <div class="card-body change-border01__inner" id="addCart1">
            
            <a href="{{ route('cart.add.auction', $auction->id) }}" class="card-link">Add to Cart</a>
        </div>

    </div>
</main>
@endsection
