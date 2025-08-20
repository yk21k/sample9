@extends('layouts.app')

@section('content')
<style>
    p {
      word-break: break-word;      /* 長い単語の途中でも改行を入れる */
      overflow-wrap: break-word;   /* word-wrapの新しいプロパティ名 */
      white-space: normal;         /* 連続スペースを折り返して表示 */
    }
</style>
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
    @elseif($winBuyer->winner_user_id)
        <div class="alert alert-danger auction-alert w-100">
            <i class="fas fa-times-circle"></i>
            <strong>オークションは￥{{ $winBuyer->final_price }}で決済され終了しました。</strong>
                <p>終了日時: {{ $winBuyer->payment_at }}</p>
        </div>    
    @else
        <div class="alert alert-info auction-alert w-100">
            <i class="fas fa-gavel"></i>
            <strong>オークションは現在進行中です。</strong>
        </div>
    @endif

    <div class="product-info-auction">
      <div class="row">
        <div class="col-md-12" style="max-width:100%;">
          <h2>{{ $auction_bid_items->name }}</h2><br>
          <h4>
            終了日：{{ Carbon::parse($auction_bid_items->end)->format('Y年m月d日 H:i') }}<br>
            --<small>{{ Carbon::parse($auction_bid_items->updated_at)->format('Y年m月d日 H:i') }}に更新されました--</small>
          </h4>
          <p>
            説明：{{ $auction_bid_items->description }}
          </p>
        </div>
        
        <div class="col-md-12">
          <p class="price-auction">
            <strong>初期価格：¥{{ number_format($auction_bid_items->suggested_price+$auction_bid_items->shipping_fee) }}円</strong>&nbsp;<small><a style="color: cadetblue;">内訳(¥{{ number_format($auction_bid_items->suggested_price) }}円 ＋ 配送料：{{number_format($auction_bid_items->shipping_fee)  }}円)</a></small>
            
          </p>
          <p class="price-auction" id="current-price-auction">
            <strong>即決価格：¥{{ number_format($auction_bid_items->spot_price+$auction_bid_items->shipping_fee) }}円</strong>&nbsp;<small><a style="color: cadetblue;">内訳(¥{{ number_format($auction_bid_items->spot_price) }}円 ＋ 配送料：{{number_format($auction_bid_items->shipping_fee) }}円)</small> </a> 
        　</p>
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
            @php
                $now = Carbon::now();
                $endDate = Carbon::parse($auction_bid_items->end);
                $oneWeekLater = $endDate->copy()->addWeek();
            @endphp
            
            @if(now()->greaterThan($auction_bid_items->end) && Auth::check() && optional($topBids->first())->user_id === Auth::id())
                <p class="text-info">
                    お客様は、入札順位一位でオークションが終了したので、購入することが可能です。
                    期限は、{{$oneWeekLater->format('Y年m月d日 H:i')}} です
                </p>
                
                @if ($now->between($endDate, $oneWeekLater))
                    <button class="btn btn-success" onclick="openPostAuctionModal()">購入手続きへ進む</button>
                @endif


                <!-- モーダル表示 -->
                <div id="postAuctionModal" style="display: none; position: fixed; top: 30%; left: 30%; width: 40%; background: #fff; border: 1px solid #aaa; padding: 20px; z-index: 1000;">
                    <p style="color: gray;">入札順位一位として、商品を購入しますか？</p>
                    <form id="winnerBuyForm" method="GET" action="{{ route('auction.payment', ['id' => $auction_bid_items->id]) }}">
                        <button type="submit" class="btn btn-primary">はい、購入手続きへ</button>
                        <button type="button" class="btn btn-secondary" onclick="closePostAuctionModal()">キャンセル</button>
                    </form>
                </div>
                <div id="postAuctionBackdrop" style="display: none; position: fixed; top: 0; left: 0; height: 100%; width: 100%; background-color: rgba(0,0,0,0.5); z-index: 999;"></div>
            
            @elseif(isset($winBuyer->winner_user_id) || now()->greaterThan($auction_bid_items->end))

                <p class="text-danger">オークションは終了しました。</p>

            @elseif(Auth::check() && optional($topBids->first())->user_id === Auth::user()->id)
                <p class="text-primary">現在入札順位一位です。</p>
            @else
                {{-- 即決入札フォーム --}}
                @if($delivery_addresses)
                    <form method="POST" action="{{ route('auction.bid.store', $auction_bid_items->id) }}" id="buyNowForm">
                        @csrf
                        <input type="hidden" name="bid_amount" value="{{ (int)($auction_bid_items->spot_price+$auction_bid_items->shipping_fee) }}">
                        <button type="button" class="btn btn-danger" onclick="openBuyNowModal()">
                            即決価格で入札（¥{{ number_format($auction_bid_items->spot_price+$auction_bid_items->shipping_fee) }}）
                        </button>
                    </form>
                @else
                    <button type="button" class="btn btn-danger" disabled>
                        即決価格で入札（¥{{ number_format($auction_bid_items->spot_price+$auction_bid_items->shipping_fee) }}）
                    </button>
                    購入には、送付先住所の登録が必要です。
                    <a href="{{ route('account.account', ['id'=>Auth::user()->id]) }}"><small>登録ページへ</small></a>
                @endif    

                {{-- 通常入札フォーム --}}
                @php
                    $suggestedPrice = $auction_bid_items->suggested_price;
                    $spotPrice = $auction_bid_items->spot_price;
                    $minimumBidUnit = $suggestedPrice >= 10000 ? 1000 : 100;
                @endphp
                <form method="POST" action="{{ route('auction.bid.store', $auction_bid_items->id) }}" id="regularBidForm" class="mt-4">
                    @csrf
                    即決金額を超える入札額は、即決金額で決済画面に進みます

                    <div class="form-group">
                        <label for="bid_amount">入札金額</label>
                        <input type="number"
                           name="bid_amount"
                           id="bid_amount"
                           class="form-control @error('bid_amount') is-invalid @enderror"
                           required
                           min="{{ (int) optional($topBids->first())->amount + $minimumBidUnit }}"
                           max="{{ (int) optional($topBids->first())->amount + $minimumBidUnit }}"
                           step="{{ $minimumBidUnit }}"
                           placeholder="¥金額を入力">


                        {{-- エラーメッセージ表示 --}}
                        @error('bid_amount')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror           
                    </div>
                    

                    <div class="form-group">
                        <label for="bid_amount_confirm">確認のためもう一度入力</label>
                        <input type="number"
                               name="bid_amount_confirm"
                               id="bid_amount_confirm"
                               class="form-control @error('bid_amount_confirm') is-invalid @enderror"
                               required
                               placeholder="もう一度同じ金額を入力">
                        {{-- エラーメッセージ表示 --}}
                        @error('bid_amount_confirm')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror           
                    </div>

                    

                    <p id="confirm-error" style="color: red; display: none;">確認金額が一致しません。</p>
                    @if($delivery_addresses)
                        <button type="submit" class="btn btn-primary" onclick="handleRegularBid()">入札する</button>
                    @else
                        <button type="submit" class="btn btn-primary" disabled>入札する</button>購入には、送付先住所の登録が必要です。
                        <a href="{{ route('account.account', ['id'=>Auth::user()->id]) }}"><small>登録ページへ</small></a>
                    @endif    
                </form>


                <!-- 即決確認モーダル -->
                <div id="confirmModal" style="display: none; position: fixed; top: 30%; left: 30%; width: 40%; background: #fff; border: 1px solid #aaa; padding: 20px; z-index: 1000;">
                    <p style="color: red;">この金額で即決入札しますか？</p>
                    <h3 style="color: red;" id="modalAmount">¥{{ number_format($auction_bid_items->spot_price+$auction_bid_items->shipping_fee) }}</h3>
                    <button id="confirmModalButtonYes" style="color: red;">はい</button>
                    <button id="confirmModalButtonNo" style="color: red;">キャンセル</button>
                </div>

                <!-- モーダル背景 -->
                <div id="modalBackdrop" style="display: none; position: fixed; top: 0; left: 0; height: 100%; width: 100%; background-color: rgba(0,0,0,0.5); z-index: 999;">
                </div>

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
        const buyNowForm = document.getElementById('buyNowForm');

        const rawDeliveryFee = @json($auction_bid_items->shipping_fee ?? 0);
        const rawBase = @json($topBids->first()->amount ?? $auction_bid_items->suggested_price)+rawDeliveryFee;
        const spotPrice = @json($auction_bid_items->spot_price);
        const auctionStart = new Date("{{ $auction_bid_items->start }}");

        const baseAmount = parseInt(rawBase, 10);
        const deliveryFee = parseInt(rawDeliveryFee, 10);
        const suggestedPrice = {{ $auction_bid_items->suggested_price }};
        const now = new Date();
        const twoDaysLater = new Date(auctionStart.getTime() + 2 * 24 * 60 * 60 * 1000);

        const minimumBidUnit = suggestedPrice >= 10000 ? 1000 : 100;
        const total = spotPrice + deliveryFee;

        // モーダル「はい」ボタン
        const yesButton = document.getElementById('confirmModalButtonYes');
        const noButton = document.getElementById('confirmModalButtonNo');
        const modal = document.getElementById('confirmModal');
        const backdrop = document.getElementById('modalBackdrop');


        let forbiddenPrices = [];

        if (now < twoDaysLater) {
            const isNotRound = total % minimumBidUnit !== 0;

            if (isNotRound) {
                // 禁止したい価格：total から100円単位で減らし、3つだけ弾く
                const max = Math.floor(total / minimumBidUnit) * minimumBidUnit;
                const count = 3; // 明示的にNGにしたい個数
                for (let i = 0; i < count; i++) {
                    const price = max - (i * minimumBidUnit);
                    if (price > baseAmount) {
                        forbiddenPrices.push(price);
                    }
                }
            }
        }


        function getMinimumBidUnit(amount) {
            if (amount >= 10000) return 1000;
            if (amount >= 1000) return 100;
            return 10;
        }

        // 入札ボタン押下時処理
        window.handleRegularBid = function () {
            const bid = parseInt(bidInput.value, 10);
            const confirm = parseInt(confirmInput.value, 10);
            const minimumUnit = getMinimumBidUnit(baseAmount);

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

            if (now < twoDaysLater && forbiddenPrices.includes(bid)) {
                alert("この金額（¥" + bid + "）では、開始から2日以内は入札できません。");
                return;
            }

            const diff = bid - baseAmount;
            if (bid !== total) { // 即決価格ちょうどでない場合のみチェック
                if (diff < minimumUnit || diff % minimumUnit !== 0) {
                    alert(
                        "現在の価格（入札基準額：" + baseAmount + " 円）より " + minimumUnit + " 円以上の増額で、" +
                        minimumUnit + " 円単位で入札してください。"
                    );
                    return;
                }
            }

            document.getElementById('regularBidForm').submit();
        };

        // 入力中に即時チェック
        bidInput.addEventListener('input', function () {
            const bid = parseInt(this.value, 10);
            if (now < twoDaysLater && forbiddenPrices.includes(bid)) {
                this.setCustomValidity("この金額では入札できません（開始から2日以内）。");
            } else {
                this.setCustomValidity("");
            }
        });

        yesButton.addEventListener('click', function () {
        const value = document.querySelector('#buyNowForm input[name="bid_amount"]').value;
        console.log('✅ 即決フォーム値:', value);

        if (!value || isNaN(value)) {
            alert("送信値が不正です。");
            return;
        }

            document.getElementById('buyNowForm').submit();
        });

        noButton.addEventListener('click', function () {
            modal.style.display = 'none';
            backdrop.style.display = 'none';
        });

        // 即決モーダル関連
        window.openBuyNowModal = function () {
            console.log("▶ openBuyNowModal が呼ばれました");

            const modal = document.getElementById('confirmModal');
            const backdrop = document.getElementById('modalBackdrop');

            if (!modal || !backdrop) {
                alert("モーダルのHTMLが存在していません。");
                console.warn("❌ confirmModalまたはmodalBackdropが見つかりません");
                return;
            }


            document.getElementById('confirmModal').style.display = 'block';
            document.getElementById('modalBackdrop').style.display = 'block';
        };

        window.closeModal = function () {
            document.getElementById('confirmModal').style.display = 'none';
            document.getElementById('modalBackdrop').style.display = 'none';
        };

        window.submitBuyNow = function () {
            const value = parseInt(document.querySelector('#buyNowForm input[name="bid_amount"]').value, 10);
            console.log('即決フォーム値:', value);  // ← これも出てない場合、ここが呼ばれていません
            document.getElementById('buyNowForm').submit();
        };


    });
</script>

<script>
    function submitBuyNow() {
        const value = document.querySelector('#buyNowForm input[name="bid_amount"]').value;
        console.log('✅ 即決フォーム値:', value);

        // 念のため NaN 判定
        if (!value || isNaN(value)) {
            alert("送信値が不正です。");
            return;
        }

        document.getElementById('buyNowForm').submit();
    }
</script>



@endsection
