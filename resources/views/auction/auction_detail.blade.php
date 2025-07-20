@extends('layouts.app')

@section('content')
<div class="container">

    {{-- 商品名 --}}
    <h1>{{ $auction_bid_items->name }}</h1>

    {{-- オークションの状態表示 --}}
    @php use Carbon\Carbon; @endphp
    @if($isAuctionNotStarted)
        <div class="alert alert-warning">
            <i class="fas fa-clock"></i>
            オークションはまだ開始されていません。<br>
            開始日時：{{ Carbon::parse($auction_bid_items->start)->format('Y年m月d日 H:i') }}
        </div>
    @elseif($isAuctionEnded)
        <div class="alert alert-danger">
            <i class="fas fa-times-circle"></i>
            オークションは終了しました。<br>
            終了日時：{{ Carbon::parse($auction_bid_items->end)->format('Y年m月d日 H:i') }}
        </div>
    @else
        <div class="alert alert-success">
            <i class="fas fa-gavel"></i>
            オークションは進行中です！<br>
            終了日時：{{ Carbon::parse($auction_bid_items->end)->format('Y年m月d日 H:i') }}
        </div>
    @endif


    {{-- 即決入札フォーム --}}
                
    <form method="POST" action="{{ route('auction.bid.store', $auction_bid_items->id) }}" id="buyNowForm">
        @csrf
        <input type="hidden" name="bid_amount" value="{{ $auction_bid_items->spot_price+$auction_bid_items->shipping_fee }}">
        <button type="button" class="btn btn-danger" onclick="openBuyNowModal()">
            即決価格で入札（¥{{ number_format($auction_bid_items->spot_price+$auction_bid_items->shipping_fee) }}）
        </button>
    </form>

    {{-- 商品動画 --}}

    @if(json_decode($auction_movies, true))
		@foreach(json_decode($auction_movies, true) as $movie)
				<video controls width="60%" src="{{ asset('storage/'.$movie['download_link']) }}" muted class="contents_width"></video>
		@endforeach
	@endif
</div>
@endsection

@php
    $spotAmount = $auction_bid_items->spot_price + $auction_bid_items->shipping_fee;
    $formattedSpotAmount = number_format($spotAmount); // PHPで整形
@endphp

<script>
    const spotAmount = "{{ $formattedSpotAmount }}";

    function openBuyNowModal() {
        if (confirm(`￥ ${spotAmount} 円で即決購入しますか？`)) {
            document.getElementById('buyNowForm').submit();
        }
    }
</script>

