@extends('layouts.app')

@section('content')


<main class="product-detail-auction-container">
    
    @php
        use Carbon\Carbon; 
    @endphp

    <!-- オークションが開始されていない場合のメッセージ -->
    @if($isAuctionNotStarted)
        <div class="alert alert-warning auction-alert w-100">
            <i class="fas fa-clock"></i> <!-- アイコン追加 -->
            <strong>オークションはまだ開始されていません。</strong>
            <p>開始日時: {{ Carbon::parse($auction_bid_items->start_time)->format('Y年m月d日 H:i') }}</p>
        </div>
    <!-- オークションが終了した場合のメッセージ -->
    @elseif($isAuctionEnded)
        <div class="alert alert-danger auction-alert w-100">
            <i class="fas fa-times-circle"></i> <!-- アイコン追加 -->
            <strong>オークションは終了しました。</strong>
            <p>終了日時: {{ Carbon::parse($auction_bid_items->end_time)->format('Y年m月d日 H:i') }}</p>
        </div>
    <!-- オークションが入札可能な場合の表示 -->
    @else
        <div class="alert alert-info auction-alert w-100">
            <i class="fas fa-gavel"></i> <!-- アイコン追加 -->
            <strong>オークションは現在進行中です。</strong>
            <p>入札できます！</p>
        </div>
    @endif
    <div class="product-info-auction">
        <div class="row">
            <div class="col-md-10">
                <h2>商品名：{{ $auction_bid_items->name }}</h2>
                <p>説明：{{ $auction_bid_items->description }}</p>
            </div>
            <div class="col-md-4">    
                <p class="price-auction">初期価格：¥  {{ number_format($auction_bid_items->suggested_price) }}</p>
                <p class="price-auction" id="current-price-auction">即決価格：¥ {{ number_format($auction_bid_items->spot_price) }}</p> <!-- 即決価格 -->
            </div>
        </div> 
    </div> 
    <div class="row">
        <!-- 2/3 幅のカルーセル -->
        <div class="col-md-6">
            <div id="carouselExample" class="carousel slide">
              <div class="carousel-inner">
                @foreach($auction_photo_movies as $auction_photo_movie)
                    @foreach(range(1, 7) as $index)
                        @if($auction_photo_movie->{'cover_img'.$index})
                            <!-- 最初の画像にのみ "active" クラスを追加 -->
                            <div class="carousel-item {{ $loop->first && $index == 1 ? 'active' : '' }}">
                                <img class="card-img-top" src="{{ asset('storage/' . str_replace(['[', ']', '"'], '', $auction_photo_movie->{'cover_img'.$index})) }}" alt="Card image cap">
                            </div>
                        @endif
                    @endforeach
                @endforeach
              </div>
              <button class="carousel-control-prev" type="button" data-bs-target="#carouselExample" data-bs-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Previous</span>
              </button>
              <button class="carousel-control-next" type="button" data-bs-target="#carouselExample" data-bs-slide="next">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Next</span>
              </button>
            </div>
        </div>
        <!-- 1/3 幅の入札履歴とフォーム -->
        <div class="col-md-6">
            <!-- 入札履歴（上位3件） -->
                <h3>入札履歴（上位3件）</h3>
            <div class="product-detail-auction">
                <table class="table">
                    <thead>
                        <tr>
                            <th>順位</th>
                            <th>入札者</th>
                            <th>金額</th>
                            <th>入札日時</th>
                            <th>操作</th> <!-- 操作の列を追加 -->
                        </tr>
                    </thead>
                    <tbody>
                        @if(session('success'))
                            <div class="alert alert-success">
                                {{ session('success') }}
                            </div>
                        @endif

                        @if(session('error'))
                            <div class="alert alert-danger">
                                {{ session('error') }}
                            </div>
                        @endif
                        @foreach($topBids as $index => $bid)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $bid->user_name }}</td>
                                <td>¥{{ number_format($bid->amount) }}</td>
                                <td>{{ Carbon::parse($bid->bid_time)->format('Y年m月d日 H:i') }}</td>
                                <td>
                                    <!-- 現在のユーザーが入札者と一致している場合のみキャンセルボタンを表示 -->
                                    @if(Auth::check() && Auth::user()->id == $bid->user_id)
                                        <form action="{{ route('auction.bid.cancel', $bid->id) }}" method="POST" onsubmit="return confirm('本当にキャンセルしますか？')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm">キャンセル</button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>    
            </div><br>
            <form action="{{ route('auction.bid.store', $auction_bid_items->id) }}" method="POST">
            @csrf
                <div class=""> 
                    <h2>入札　フィールド</h2> 
                    <!-- 即決金額が設定されている場合、即決ボタンを表示 -->
                    @if($isBuyNow)
                        <input type="hidden" name="bid_amount" value="{{ $auction_bid_items->buy_now_price }}">
                        <button type="submit" class="btn btn-success">即決価格で入札</button>
                    @else
                        <button type="submit" class="btn-bid-auction">入札する</button>
                    @endif  
                    <!-- 入札金額を入力するフィールド -->
                    <input type="text" name="bid_amount" id="bid_amount" placeholder="入札金額を入力" required>

                    <!-- 入札確認用のフィールド -->
                    <input type="text" name="bid_amount_confirm" id="bid_amount_confirm" placeholder="確認のためもう一度入力" required>

                    <button type="submit" class="btn-bid-auction">入札する</button>
                </div>
            </form>    
        </div>
        <br>      
        <div id="bid-message" class="bid-message"></div>
        <div id="error-message" class="error-message"></div>    
    </div>    


</main>


<script>
    document.addEventListener('DOMContentLoaded', function () {
    const bidButton = document.querySelector('.btn-bid-auction');
    const bidMessage = document.querySelector('#bid-message');
    const errorMessage = document.querySelector('#error-message');
    const currentPriceElement = document.querySelector('#current-price-auction');
    const bidAmountField = document.querySelector('#bid_amount');
    const bidAmountConfirmField = document.querySelector('#bid_amount_confirm');
    let currentPrice = parseInt(currentPriceElement.textContent.replace('¥', '').replace(/,/g, ''), 10); // 現在価格の取得

    // 入札するボタンがクリックされたときの処理
    if (bidButton) {
        bidButton.addEventListener('click', function (event) {
            // 入力された金額を取得
            const bidAmount = bidAmountField.value.trim();
            const bidAmountConfirm = bidAmountConfirmField.value.trim();

            // 金額の一致確認
            if (bidAmount !== bidAmountConfirm) {
                // 入力金額が一致しない場合、エラーメッセージを表示
                errorMessage.textContent = "2回目の入札額が一致しません。もう一度確認してください。";
                errorMessage.style.color = 'red';
                errorMessage.style.fontSize = '18px';
                bidMessage.textContent = ''; // 成功メッセージは非表示に
                event.preventDefault(); // フォーム送信をキャンセル
                return;
            }

            // 入札額が数字として有効か確認
            if (bidAmount && !isNaN(bidAmount)) {
                let bid = parseInt(bidAmount.replace(/,/g, '').replace('¥', '').trim(), 10);

                // 入札額が現在価格より高い場合
                if (bid > currentPrice) {
                    // 新しい価格を反映
                    currentPrice = bid;  // 入札後の新しい価格を現在価格として更新

                    // 価格の表示を更新
                    currentPriceElement.textContent = `¥ ${bid.toLocaleString()}`;

                    // メッセージを表示
                    bidMessage.textContent = `¥${bid.toLocaleString()}で入札しました！`;
                    bidMessage.style.zIndex = '9999'; 
                    bidMessage.style.color = 'blue';
                    bidMessage.style.fontSize = '20px';
                    bidMessage.style.transition = 'all 0.5s ease';
                    bidMessage.style.display = 'block'; // 明示的に表示を変更

                    // メッセージの消失（5秒後）
                    setTimeout(function () {
                        bidMessage.style.display = 'none'; // メッセージを非表示
                    }, 5000);
                    console.log(${bid.toLocaleString()});
                    // エラーメッセージをリセット
                    errorMessage.textContent = '';
                } else {
                    bidMessage.textContent = ''; // 入札が不正な場合はメッセージを消す
                    errorMessage.textContent = "現在価格よりも高い金額を入力してください。";
                    errorMessage.style.color = 'red';
                    errorMessage.style.fontSize = '18px';
                    event.preventDefault(); // フォーム送信をキャンセル
                }
            } else {
                errorMessage.textContent = "無効な金額です。再度入力してください。";
                errorMessage.style.color = 'red';
                bidMessage.textContent = ''; // 入札額が無効な場合はメッセージを消す
                event.preventDefault(); // フォーム送信をキャンセル
            }
        });
    }
    });
</script>





@endsection
