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

    @if ($auction)
        <div class="container mt-4">
            <h2>{{ $auction->name }}</h2>
            <p>{{ $auction->description }}</p>

            <ul class="list-group">
                <li class="list-group-item">開始日時：{{ $auction->start }}</li>
                <li class="list-group-item">終了日時：{{ $auction->end }}</li>
                <li class="list-group-item">希望価格：¥{{ number_format($auction->suggested_price) }}</li>
                <li class="list-group-item">即決価格：¥{{ number_format($auction->spot_price) }}</li>
                <li class="list-group-item">落札価格：{{ $auction->final_price ? '¥' . number_format($auction->final_price) : '未落札' }}</li>

                <li class="list-group-item">
                    支払い状況：
                    @if ($auction->payment_status === 'paid')
                        <span class="badge bg-success">支払い済</span>（{{ $auction->payment_at }}, {{ $auction->payment_method }}）
                    @elseif ($auction->payment_status === 'pending')
                        <span class="badge bg-warning">支払い待ち</span>
                    @else
                        <span class="badge bg-danger">支払い失敗</span>
                    @endif
                </li>

                <li class="list-group-item">
                    落札者：
                    @if ($auction->winner_user_id)
                        {{ optional($auction->winner)->name ?? '不明なユーザー' }}
                    @else
                        ー
                    @endif
                </li>
            </ul>
        </div>
    @endif
</main>
        
        
    </main>

    <footer>
        <p>&copy; 2025 オークションサイト</p>
    </footer>

    <script></script>
</body>
</html>


@endsection