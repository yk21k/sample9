@extends('layouts.app')

@section('content')

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>オークションサイト</title>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <style>
        p {
            word-break: break-word;
            overflow-wrap: break-word;
            white-space: normal;
                    
        }
        .limited-description {
          max-height: 150px !important;
          overflow-y: auto !important;
          word-break: break-word !important;
        }
        .limited-description::-webkit-scrollbar {
            width: 15px;               /* スクロールバーの幅 */
        }

        .limited-description::-webkit-scrollbar-track {
            background: #f0f0f0;       /* トラックの色（スクロールバーの背景） */
        }

        .limited-description::-webkit-scrollbar-thumb {
            background-color: #4A90E2; /* つまみの色 */
            border-radius: 6px;        /* つまみの角丸 */
            border: 3px solid #f0f0f0; /* つまみの周りの余白（トラック色と同じにする） */
        }

        .limited-description::-webkit-scrollbar-thumb:hover {
            background-color: #357ABD; /* ホバー時のつまみの色 */
        }

    </style>
</head>
<body>
    <header>
        <h1>オークションサイト</h1>   
    </header>

    
<main>
    <div class="product-list-auction">
        @foreach($auction_items as $auction_item)
            @if($auction_item->event_ended == 1)
                <div class="product-card-auction" >
                    <img class="card-img-top" src="{{ asset('storage/' . str_replace(['[', ']', '"'], '', $auction_item->cover_img1)) }}" alt="Card image cap">
                    <h2>{{ $auction_item->name }}</h2>
                    <p class="limited-description">{{ $auction_item->description }}</p>
                    <p class="price-auction">決済価格　¥{{ $auction_item->final_price }}</p>
                    <a>決済され終了しました</a>
                </div>
            @else
                <div class="product-card-auction">
                    <img class="card-img-top" src="{{ asset('storage/' . str_replace(['[', ']', '"'], '', $auction_item->cover_img1)) }}" alt="Card image cap">
                    <h2>{{ $auction_item->name }}</h2>
                    <p class="limited-description">{{ $auction_item->description }}</p>
                    <p class="price-auction">¥{{ $auction_item->suggested_price }}</p>
                    <a href="{{ route('home.auction.show', $auction_item->id) }}" class="btn-details-auction">詳細を見る</a>
                </div>
            @endif
        @endforeach
    </div>

    @if ($auction)
        <div class="container mt-4">
            <h2>{{ $auction->name }}</h2>
            <p class="limited-description">{{ $auction->description }}</p>

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