<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Auction;
use App\Models\DeliveryAddress;
use App\Models\Bid;
use App\Models\AuctionOrder;

use Auth;
use Carbon\Carbon;

use Stripe\Stripe;
use Stripe\PaymentIntent;
use Illuminate\Support\Facades\Log;


class AuctionController extends Controller
{
    public function auction_index($id = null){

        $auction_items = Auction::where('status', 1)->get();
        $auction_photo_movies = Auction::where('status', 1)->get('cover_img1', 'cover_img2', 'cover_img3', 'cover_img4', 'cover_img5', 'cover_img6', 'cover_img7', 'movie', 'spot_price');

        $auction = null;
        if ($id) {
            $auction = $id ? Auction::with('winner')->find($id) : null;

        }

        return view('auction.auction_index', ['auction_items' => $auction_items, 'auction_photo_movies' => $auction_photo_movies, 'auction' => $auction]);
    }

    public function auction_show(Request $request, $id){
        // dd($id);

        if (!auth()->check()) {
            return redirect()->route('login')->with('error', 'ログインが必要です');
        }

        $delivery_addresses = DeliveryAddress::where('user_id', auth()->user()->id)->first();
        // dd($delivery_addresses->shipping_address);

        $auction_photo_movies = Auction::where('status', 1)->where('id', $id)->get(['cover_img1', 'cover_img2', 'cover_img3', 'cover_img4', 'cover_img5', 'cover_img6', 'cover_img7', 'movie', 'spot_price']);
        // dd($auction_photo_movies);

        $auction_bid_items = Auction::find($id);
        // dd($auction_bid_items);
        // dd($auction_bid_items['name']);
        // dd($auction_bid_items->name);
        if (!$auction_bid_items) {
            abort(404, 'オークションが見つかりません');
        }

        $winBuyer = Auction::where('id', $id)->first();
        // dd($winBuyer);

        // 即決金額（Buy Now）が設定されている場合、即決のボタンが表示されます。
        // $isBuyNow = $auction_bid_items->spot_price ? true : false;

        $isBuyNow = optional($auction_bid_items)->spot_price ? true : false;


        $current_time = now();

        // $isAuctionNotStarted = $current_time < $auction_bid_items->start;
        $isAuctionNotStarted = $current_time < optional($auction_bid_items)->start;

        // $isAuctionEnded = $current_time > $auction_bid_items->end;
        $isAuctionEnded = $current_time > optional($auction_bid_items)->end;


        $now = Carbon::now();
        $start = Carbon::parse($auction_bid_items->start);
        $twoDaysLater = $start->copy()->addDays(2);


        $spotPrice = $auction_bid_items->spot_price;
        $shippingFee = $auction_bid_items->shipping_fee;
        $minimumBidUnit = $auction_bid_items->suggested_price >= 10000 ? 1000 : 100;

        $total = $spotPrice + $shippingFee;

        // 最後の1単位分差し引いた金額（= よくあるNGパターン）
        $forbidden1 = $total - $minimumBidUnit;

        // 割り切れない端数がある場合、その直前の金額もNGとする
        $remainder = $total % $minimumBidUnit;
        $forbidden2 = $remainder > 0 ? $total - $remainder : null;

        $forbiddenPrices = array_filter([
            $forbidden1,
            $forbidden2,
            // $total, // 即決価格ちょうども禁止
        ]);

        // バリデーション
        if ($now->lt($twoDaysLater) && in_array((int)$request->bid_amount, $forbiddenPrices, true)) {
            return back()->withErrors([
                'bid_amount' => 'この金額では入札できません（開始から2日以内のため）。',
            ])->withInput();
        }


        $topBids = collect();

        if ($auction_bid_items && $auction_bid_items->end) {
            $topBids = Bid::where('auction_id', $auction_bid_items->id)
                          ->where('bid_time', '<=', $auction_bid_items->end)
                          ->orderByDesc('amount')
                          ->take(3)
                          ->get(['id', 'amount', 'bid_time', 'user_id']);
        }

         // ユーザー名を含む入札情報を取得
        foreach ($topBids as $bid) {
            $bid->user_name = $bid->user->name; // ユーザー名を追加

        }
        // dd(Bid::where('auction_id', $auction_bid_items->id)->get());

        return view('auction.auction_show', ['auction_photo_movies' => $auction_photo_movies, 'auction_bid_items' => $auction_bid_items, 'id' => $id, 'topBids' => $topBids, 'isAuctionNotStarted' => $isAuctionNotStarted, 'isAuctionEnded' => $isAuctionEnded, 'isBuyNow' => $isBuyNow, 'delivery_addresses' => $delivery_addresses, 'winBuyer' => $winBuyer]);
    }

    public function storeBid(Request $request, Auction $auction)
    {
        // 1. 終了していたら弾く
        if (now()->greaterThan($auction->end)) {
            \Log::info('オークション終了後の入札試行');
            return redirect()->back()->withErrors(['auction' => 'オークションは終了しました。']);
        }

        $bidAmount = (int) $request->input('bid_amount');

        // $currentTopBid = Bid::where('auction_id', $auction->id)->orderByDesc('amount')->first();
        // $baseAmount = $currentTopBid->amount ?? $auction->suggested_price;
        
        $currentTopBid = Bid::where('auction_id', $auction->id)->orderByDesc('amount')->first();
        $baseAmount = (int)($currentTopBid ? $currentTopBid->amount : $auction->suggested_price);

        $totalPrice = $auction->spot_price + $auction->shipping_fee;

        $shipFee = (int)($auction->shipping_fee);

        // dd($currentTopBid->amount);

        \Log::debug('即決分岐チェック', [
            'bidAmount' => $bidAmount,
            'totalPrice' => $totalPrice,
            'equals' => $bidAmount === $totalPrice,
            'test' => $bidAmount >= $totalPrice,
            'user_id' => Auth::id(),
        ]);
        // dd($auction);

        // 2. ✅ 即決価格ちょうどならそのまま支払いへ
        if ($bidAmount >= $totalPrice) {
            return redirect()->route('auction.payment', ['id' => $auction->id])
                             ->with('bid_amount', $bidAmount)
                             ->with('auction', $auction);
        }

        // 3. 入札単位チェック
        $minimumUnit = (int)($baseAmount >= 10000 ? 1000 : ($baseAmount >= 1000 ? 100 : 10));
        $diff = $bidAmount - $baseAmount;

        if ($diff < $minimumUnit || $diff % $minimumUnit !== 0) {
            return redirect()->back()->withErrors([
                'bid_amount' => "最低入札単位は {$minimumUnit} 円です。現在の価格（¥" . number_format($baseAmount) . "）より ¥{$minimumUnit} 以上増額し、かつ単位を守ってください。"
            ])->withInput();
        }

        $allowedBid = $baseAmount + $minimumUnit + $shipFee;

        // 4. フォーマットバリデーション（Laravel側としては形式の保証）
        $request->validate([
            'bid_amount' => ['required', 'integer', 'in:' . $allowedBid],
            'bid_amount_confirm' => ['required', 'same:bid_amount'],
        ], [
            'bid_amount.required' => '入札金額を入力してください。',
            'bid_amount.integer' => '金額は整数で入力してください（例：11000）。',
            'bid_amount.in' => '現在の即決金額以外の入札可能額は ¥' . number_format($allowedBid) . ' です。',
            'bid_amount_confirm.required' => '確認金額を入力してください。',
            'bid_amount_confirm.same' => '確認金額が一致しません。',
        ]);

        // 5. 入札保存
        $bid = new Bid();
        $bid->auction_id = $auction->id;
        $bid->user_id = Auth::id();
        $bid->amount = $bidAmount;
        $bid->bid_time = now();
        $bid->save();

        return redirect()->route('home.auction.show', $auction->id)
                         ->with('success', '入札が完了しました。');
    }

    // public function storeBid(Request $request, Auction $auction)
    // {
    //     // 1. オークション終了確認
    //     if (now()->greaterThan($auction->end)) {
    //         \Log::info('オークション終了後の入札試行');
    //         return redirect()->back()->withErrors(['auction' => 'オークションは終了しました。']);
    //     }

    //     $bidAmount = (int) $request->input('bid_amount');

    //     // 2. 現在の最高入札額を取得
    //     $currentTopBid = Bid::where('auction_id', $auction->id)->orderByDesc('amount')->first();
    //     $baseAmount = $currentTopBid ? $currentTopBid->amount : $auction->suggested_price;

    //     $totalPrice = $auction->spot_price + $auction->shipping_fee;

    //     \Log::debug('即決分岐チェック', [
    //         'bidAmount' => $bidAmount,
    //         'totalPrice' => $totalPrice,
    //         'equals' => $bidAmount === $totalPrice,
    //         'test' => $bidAmount >= $totalPrice,
    //         'user_id' => Auth::id(),
    //     ]);

    //     // 3. 即決チェック
    //     if ($bidAmount >= $totalPrice) {
    //         return redirect()->route('auction.payment', ['id' => $auction->id])
    //                          ->with('bid_amount', $bidAmount)
    //                          ->with('auction', $auction);
    //     }

    //     // 4. 入札単位の決定
    //     $minimumUnit = $baseAmount >= 10000 ? 1000 : ($baseAmount >= 1000 ? 100 : 10);
        
    //     // ここを明示的に整数にするのがポイント
    //     $allowedBid = (int) ($baseAmount + $minimumUnit);

    //     // 5. バリデーション：許可された額しか通さない
    //     $request->validate([
    //         'bid_amount' => ['required', 'integer', 'in:' . $allowedBid],
    //         'bid_amount_confirm' => ['required', 'same:bid_amount'],
    //     ], [
    //         'bid_amount.required' => '入札金額を入力してください。',
    //         'bid_amount.integer' => '金額は整数で入力してください（例：11000）。',
    //         'bid_amount.in' => '入札可能額は ¥' . number_format($allowedBid+$auction->shipping_fee) . ' のみです。',
    //         'bid_amount_confirm.required' => '確認金額を入力してください。',
    //         'bid_amount_confirm.same' => '確認金額が一致しません。',
    //     ]);

    //     // 6. 入札保存
    //     $bid = new Bid();
    //     $bid->auction_id = $auction->id;
    //     $bid->user_id = Auth::id();
    //     $bid->amount = $bidAmount;
    //     $bid->bid_time = now();
    //     $bid->save();

    //     return redirect()->route('home.auction.show', $auction->id)
    //                      ->with('success', '入札が完了しました。');
    // }


    public function payment($id)
    {
        // オークションデータを取得
        $auction = Auction::find($id);
        
        // セッションから入札額を取得
        $bidAmount = session('bid_amount');
        
        // 決済画面で表示する内容
        return view('auction.payment', [
            'auction' => $auction,
            'bidAmount' => $bidAmount
        ]);
    }

    public function charge(Request $request, $id)
    {
        Stripe::setApiKey(env('STRIPE_SECRET'));

        $auction = Auction::findOrFail($id);

        

        // 優先順：セッションの入札額 → 即決金額
        $bidAmount = session('bid_amount') ?? ($auction->spot_price + ($auction->shipping_fee ?? 0));


        if (!$bidAmount || !is_numeric($bidAmount)) {
            return redirect()->back()->with('error', '決済金額が不正です。');
        }

        try {
            $paymentIntent = PaymentIntent::create([
                'amount' => intval($bidAmount), // 最小単位
                'currency' => 'jpy',
                'payment_method' => $request->payment_method,
                'confirmation_method' => 'manual',
                'confirm' => true,
                'return_url' => route('auction.payment.success'),
            ]);

            if ($paymentIntent->status === 'requires_action') {
                // 3Dセキュア認証が必要
                return response()->json([
                    'requires_action' => true,
                    'payment_intent_client_secret' => $paymentIntent->client_secret,
                ]);
            }

            $delivery_addresses = DeliveryAddress::where('user_id', auth()->user()->id)->first();

            // 決済成功ーーーー
            $auction->update([
                'payment_method' => 'completed',
                'payment_at' => now(),
                'winner_user_id' => auth()->id(),
                'shipping_fullname' => $delivery_addresses->shipping_fullname,
                'shipping_address' => $delivery_addresses->shipping_address,
                'shipping_city' => $delivery_addresses->shipping_city,
                'shipping_state' => $delivery_addresses->shipping_state,
                'shipping_zipcode' => $delivery_addresses->shipping_zipcode,
                'shipping_phone' => $delivery_addresses->shipping_phone,
                'event_ended' => 2,
                'final_price' => $bidAmount,
            ]);

            $auction_order = new AuctionOrder;

            $auction_order->winner_user_id = auth()->id();
            $auction_order->shop_id = $auction->shop_id;
            $auction_order->auction_id = $id;
            $auction_order->payment_at = now();
            $auction_order->shipping_fullname = $delivery_addresses->shipping_fullname;
            $auction_order->shipping_address = $delivery_addresses->shipping_address;
            $auction_order->shipping_city = $delivery_addresses->shipping_city;
            $auction_order->shipping_state = $delivery_addresses->shipping_state;
            $auction_order->shipping_zipcode = $delivery_addresses->shipping_zipcode;
            $auction_order->shipping_phone = $delivery_addresses->shipping_phone;
            $auction_order->final_price = $bidAmount;
            $auction_order->shipping_fee = $auction->shipping_fee;

            // dd($auction_order);

            $auction_order->save();



            return redirect()->route('auction.payment.success')->with('success', '決済が完了しました。');

        } catch (\Exception $e) {
            return back()->with('error', '決済中にエラーが発生しました：' . $e->getMessage());
        }
    }

    public function success()
    {
        return view('auction.success'); // 成功時のビューを表示（例: resources/views/auction/success.blade.php）
    }

    public function cancelBid($bidId)
    {
        // 入札を検索
        $bid = Bid::findOrFail($bidId);

        // 入札者が自分自身であることを確認
        if (Auth::id() === $bid->user_id) {
            $bid->delete();
            return redirect()->back()->with('message', '入札がキャンセルされました。');
        }

        return redirect()->back()->with('error', 'この入札はキャンセルできません。');
    }

    public function auction_detail(Request $request, $id)
    {
        // 対象オークション商品を取得
        $auction_bid_items = Auction::find($id);

        if (!$auction_bid_items) {
            abort(404, 'オークションが見つかりません');
        }

        // 商品画像・動画などを取得（status=1で限定したい場合は条件を追加）
        $auction_photo_movies = Auction::where('id', $id)->get([
            'cover_img1', 'cover_img2', 'cover_img3',
            'cover_img4', 'cover_img5', 'cover_img6', 'cover_img7',
            'movie', 'spot_price'
        ]);

        $auction_videos = Auction::find($id)->toArray();
        // dd($auction_videos);
        $auction_movies = [];

        $auction_movies = $auction_videos['movie'];

        // 現在時刻と比較して状態を判定
        $current_time = now();

        $isAuctionNotStarted = $current_time < $auction_bid_items->start;
        $isAuctionEnded = $current_time > $auction_bid_items->end;

        // 即決価格の有無
        $isBuyNow = $auction_bid_items->spot_price ? true : false;

        // 上位3名の入札情報を取得（終了前までの入札）
        $topBids = Bid::where('auction_id', $auction_bid_items->id)
                      ->where('bid_time', '<=', $auction_bid_items->end)
                      ->orderByDesc('amount')
                      ->take(3)
                      ->get(['id', 'amount', 'bid_time', 'user_id']);

        // ユーザー名を付加（Eager Loadingしていない前提）
        foreach ($topBids as $bid) {
            $bid->user_name = optional($bid->user)->name ?? '匿名';
        }

        // ビューにすべてのデータを渡す
        return view('auction.auction_detail', [
            'auction_photo_movies' => $auction_photo_movies,
            'auction_bid_items' => $auction_bid_items,
            'topBids' => $topBids,
            'isAuctionNotStarted' => $isAuctionNotStarted,
            'isAuctionEnded' => $isAuctionEnded,
            'isBuyNow' => $isBuyNow,
            'auction_movies' =>$auction_movies
        ]);
    }

    public function confirmDelivery(Request $request, $id)
    {
        $request->validate([
            'arrival_message' => 'nullable|string|max:1000',
        ]);

        $auctionOrder = AuctionOrder::where('auction_id', $id)->firstOrFail();

        // 関連 Auction を取得（リレーションがある前提）

        // AuctionOrder の更新
        $auctionOrder->arrival_message = $request->input('arrival_message');
        $auctionOrder->arrival_confirmed_at = now();
        $auctionOrder->arrival_status = 1;
        $auctionOrder->save();

        // Auction の更新（関連が存在する場合のみ）
        if ($auctionOrder->auction) {
            $auctionOrder->arrival_message = $request->input('arrival_message');
            $auctionOrder->auction->arrival_status = 1;
            $auctionOrder->auction->arrival_confirmed_at = now();
            $auctionOrder->auction->save();
        } else {
            \Log::warning('Auction not found for AuctionOrder ID: ' . $auctionOrder->id);
        }

        return redirect()->back()->with('success', '到着確認とメッセージを保存しました。');
    }

    






}
