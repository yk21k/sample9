@extends('layouts.app')

@section('content')

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>オークションサイト</title>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
</head>
<body>
    <header>
        <h1>オークションサイト</h1>   
    </header>

    <main>
        <div class="product-list-auction">
            @foreach($auction_items as $auction_item)
            <div class="product-card-auction">
                <img class="card-img-top" src="{{ asset('storage/' . str_replace(['[', ']', '"'], '', $auction_item->cover_img1)) }}" alt="Card image cap">
                <h2>{{ $auction_item->name }}</h2>
                <p>{{ $auction_item->description }}</p>
                <p class="price-auction">¥{{ $auction_item->suggested_price }}</p>
                <a href="{{ route('home.auction.show', $auction_item->id) }}" class="btn-details-auction">詳細を見る</a>
            </div>
            @endforeach
        </div>
    </main>

    <footer>
        <p>&copy; 2025 オークションサイト</p>
    </footer>

    <script></script>
</body>
</html>


@endsection