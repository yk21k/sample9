@extends('layouts.app')

@section('content')


<main class="product-detail-auction-container">
    
    @php
        use Carbon\Carbon;
        $startTime = optional($auction_bid_items)->start_time;
        $endTime = optional($auction_bid_items)->end_time;
    @endphp

    @if($isAuctionNotStarted)
        <div class="alert alert-warning auction-alert w-100">
            <i class="fas fa-clock"></i>
            <strong>オークションはまだ開始されていません。</strong>
            @if($startTime)
                <p>開始日時: {{ Carbon::parse($startTime)->format('Y年m月d日 H:i') }}</p>
            @endif
        </div>
    @elseif($isAuctionEnded)
        <div class="alert alert-danger auction-alert w-100">
            <i class="fas fa-times-circle"></i>
            <strong>オークションは終了しました。</strong>
            @if($endTime)
                <p>終了日時: {{ Carbon::parse($endTime)->format('Y年m月d日 H:i') }}</p>
            @endif
        </div>
    @else
        <div class="alert alert-info auction-alert w-100">
            <i class="fas fa-gavel"></i>
            <strong>オークションは現在進行中です。</strong>
            <p>入札できます！</p>
        </div>
    @endif

    <div class="product-info-auction">
        <div class="row">
            <div class="col-md-15">
                <h2>{{ $auction_bid_items->name }}</h2><br>
                <h4>終了日：{{ Carbon::parse($auction_bid_items->end)->format('Y年m月d日 H:i') }} <br>--<small>{{ Carbon::parse($auction_bid_items->updated_at)->format('Y年m月d日 H:i') }}に更新されました--</small></h4>
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
                                <a class="" href="{{ route('home.auction.detail', $auction_bid_items->id) }}">

                                    <img class="card-img-top" src="{{ asset('storage/' . str_replace(['[', ']', '"'], '', $auction_photo_movie->{'cover_img'.$index})) }}" alt="Card image cap">
                                </a>    
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

            

            @if(now()->greaterThan($auction_bid_items->end) && Auth::check() && optional($topBids->first())->user_id === Auth::id())
                <p class="text-info">お客様は、入札順位一位でオークションが終了したので、購入することが可能です。</p>
                
                <button class="btn btn-success" onclick="openPostAuctionModal()">購入手続きへ進む</button>

                <!-- モーダル表示 -->
                <div id="postAuctionModal" style="display: none; position: fixed; top: 30%; left: 30%; width: 40%; background: #fff; border: 1px solid #aaa; padding: 20px; z-index: 1000;">
                    <p style="color: gray;">入札順位一位として、商品を購入しますか？</p>
                    <form id="winnerBuyForm" method="GET" action="{{ route('auction.payment', ['id' => $auction_bid_items->id]) }}">
                        <button type="submit" class="btn btn-primary">はい、購入手続きへ</button>
                        <button type="button" class="btn btn-secondary" onclick="closePostAuctionModal()">キャンセル</button>
                    </form>
                </div>
                <div id="postAuctionBackdrop" style="display: none; position: fixed; top: 0; left: 0; height: 100%; width: 100%; background-color: rgba(0,0,0,0.5); z-index: 999;"></div>
            
            @elseif(now()->greaterThan($auction_bid_items->end))
                <p class="text-danger">オークションは終了しました。</p>

            @elseif(Auth::check() && optional($topBids->first())->user_id === Auth::user()->id)
                <p class="text-primary">現在入札順位一位です。</p>
            @else
                {{-- 即決入札フォーム --}}
                <form method="POST" action="{{ route('auction.bid.store', $auction_bid_items->id) }}" id="buyNowForm">
                    @csrf
                    <input type="hidden" name="bid_amount" value="{{ $auction_bid_items->spot_price }}">
                    <button type="button" class="btn btn-danger" onclick="openBuyNowModal()">
                        即決価格で入札（¥{{ number_format($auction_bid_items->spot_price) }}）
                    </button>
                </form>

                {{-- 通常入札フォーム --}}
                
                <form method="POST" action="{{ route('auction.bid.store', $auction_bid_items->id) }}" id="regularBidForm" class="mt-4">
                    @csrf

                    <div class="form-group">
                        <label for="bid_amount">入札金額</label>
                        <input type="number"
                               name="bid_amount"
                               id="bid_amount"
                               class="form-control"
                               required
                               min="{{ $auction_bid_items->suggested_price }}"
                               placeholder="¥金額を入力">
                    </div>

                    <div class="form-group">
                        <label for="bid_amount_confirm">確認のためもう一度入力</label>
                        <input type="number"
                               name="bid_amount_confirm"
                               id="bid_amount_confirm"
                               class="form-control"
                               required
                               placeholder="もう一度同じ金額を入力">
                    </div>

                    <p id="confirm-error" style="color: red; display: none;">確認金額が一致しません。</p>

                    <button type="button" class="btn btn-primary" onclick="handleRegularBid()">入札する</button>
                </form>
                
                <!-- 即決確認モーダル -->
                <div id="confirmModal" style="display: none; position: fixed; top: 30%; left: 30%; width: 40%; background: #fff; border: 1px solid #aaa; padding: 20px; z-index: 1000;">
                    <p style="color: red;">この金額で即決入札しますか？</p>
                    <h3 style="color: red;" id="modalAmount">¥{{ number_format($auction_bid_items->spot_price) }}</h3>
                    <button onclick="submitBuyNow()" style="color: red;">はい</button>
                    <button onclick="closeModal()" style="color: red;">キャンセル</button>
                </div>

                <!-- モーダル背景 -->
                <div id="modalBackdrop" style="display: none; position: fixed; top: 0; left: 0; height: 100%; width: 100%; background-color: rgba(0,0,0,0.5); z-index: 999;"></div>

            @endif



        </div>
        <br>      
        <div id="bid-message" class="bid-message"></div>
        <div id="error-message" class="error-message"></div>    
    </div>    


</main>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const bidInput = document.getElementById('bid_amount');
        const confirmInput = document.getElementById('bid_amount_confirm');
        const confirmError = document.getElementById('confirm-error');

        const rawBase = @json($topBids->first()->amount ?? $auction_bid_items->suggested_price);
        const baseAmount = parseInt(rawBase, 10);

        function getMinimumBidUnit(amount) {
            if (amount >= 10000) return 1000;
            if (amount >= 1000) return 100;
            return 10;
        }

        window.handleRegularBid = function () {
            // alert("クリックされました");

            const bid = parseInt(bidInput.value, 10);
            const confirm = parseInt(confirmInput.value, 10);
            const minimumUnit = getMinimumBidUnit(baseAmount);

            // console.log("bid:", bid);
            // console.log("confirm:", confirm);
            // console.log("baseAmount:", baseAmount);
            // console.log("minimumUnit:", minimumUnit);

            if (isNaN(bid) || isNaN(confirm)) {
                alert("金額を正しく入力してください。");
                confirmError.style.display = 'block';
                return;
            }

            if (bid !== confirm) {
                confirmError.style.display = 'block';
                return;
            } else {
                confirmError.style.display = 'none';
            }

            const diff = bid - baseAmount;

            if (diff < minimumUnit || diff % minimumUnit !== 0) {
                alert(
                    // "最低入札単位は " + minimumUnit + " 円です。\n" +
                    "現在の価格（" + baseAmount + " 円）より " + minimumUnit + " 円以上の増額で、" +
                    minimumUnit + " 円単位で入札してください。"
                );
                return;
            }

            document.getElementById('regularBidForm').submit();
        };

        window.openBuyNowModal = function () {
            document.getElementById('confirmModal').style.display = 'block';
            document.getElementById('modalBackdrop').style.display = 'block';
        };

        window.closeModal = function () {
            document.getElementById('confirmModal').style.display = 'none';
            document.getElementById('modalBackdrop').style.display = 'none';
        };

        window.submitBuyNow = function () {
            document.getElementById('buyNowForm').submit();
        };
    });
</script>
<script>
    function openPostAuctionModal() {
        document.getElementById('postAuctionModal').style.display = 'block';
        document.getElementById('postAuctionBackdrop').style.display = 'block';
    }

    function closePostAuctionModal() {
        document.getElementById('postAuctionModal').style.display = 'none';
        document.getElementById('postAuctionBackdrop').style.display = 'none';
    }
</script>










@endsection
