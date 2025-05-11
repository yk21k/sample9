<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Auction;
use App\Models\Bid;
use Auth;

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
        $auction_photo_movies = Auction::where('status', 1)->where('id', $id)->get(['cover_img1', 'cover_img2', 'cover_img3', 'cover_img4', 'cover_img5', 'cover_img6', 'cover_img7', 'movie', 'spot_price']);
        // dd($auction_photo_movies);

        $auction_bid_items = Auction::find($id);
        // dd($auction_bid_items);
        // dd($auction_bid_items['name']);
        // dd($auction_bid_items->name);
        if (!$auction_bid_items) {
            abort(404, 'オークションが見つかりません');
        }

        // 即決金額（Buy Now）が設定されている場合、即決のボタンが表示されます。
        // $isBuyNow = $auction_bid_items->spot_price ? true : false;

        $isBuyNow = optional($auction_bid_items)->spot_price ? true : false;


        $current_time = now();

        // $isAuctionNotStarted = $current_time < $auction_bid_items->start;
        $isAuctionNotStarted = $current_time < optional($auction_bid_items)->start;


        // $isAuctionEnded = $current_time > $auction_bid_items->end;
        $isAuctionEnded = $current_time > optional($auction_bid_items)->end;


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


        return view('auction.auction_show', ['auction_photo_movies' => $auction_photo_movies, 'auction_bid_items' => $auction_bid_items, 'id' => $id, 'topBids' => $topBids, 'isAuctionNotStarted' => $isAuctionNotStarted, 'isAuctionEnded' => $isAuctionEnded, 'isBuyNow' => $isBuyNow]);
    }

    // public function storeBid(Request $request, Auction $auction)
    // {

    //     // 終了時間のチェック（再取得せず、そのまま使う）
    //     if (now()->greaterThan($auction->end)) {
    //         return redirect()->back()->withErrors(['auction' => 'オークションは終了しました。']);
    //     }

    //     // 入札金額のバリデーション
    //     $request->validate([
    //         'bid_amount' => 'required|numeric|min:' . $auction->suggested_price,
    //     ]);

    //     // 即決価格を超えた場合は支払いへ
    //     if ($request->bid_amount >= $auction->spot_price) {
    //         return redirect()->route('auction.payment', ['id' => $auction->id])
    //             ->with('bid_amount', $request->bid_amount)
    //             ->with('auction', $auction);
    //     }

    //     // 入札保存
    //     $bid = new Bid();
    //     $bid->auction_id = $auction->id;
    //     $bid->user_id = Auth::id();
    //     $bid->amount = $request->bid_amount;
    //     $bid->bid_time = now();
    //     $bid->save();

    //     return redirect()->route('home.auction.show', $auction->id)
    //         ->with('success', '入札が完了しました。');
    // }

    public function storeBid(Request $request, Auction $auction)
    {
        // オークション終了チェック
        if (now()->greaterThan($auction->end)) {

            // オークション終了後の処理

            // 入札者のリストを取得（オークションの入札者）
            $bidders = Bid::where('auction_id', $auction->id)->get();

            // 最後の入札者（最高入札者）を取得
            $winner = Bid::where('auction_id', $auction->id)
                         ->orderByDesc('amount')
                         ->first();

            // 入札者にメール通知を送信
            foreach ($bidders as $bidder) {
                // 最後の入札者にメールを送信
                if ($winner && $winner->user && $winner->user->email) {
                    Mail::to($winner->user->email)->send(new AuctionEndedMail($auction, $winner->user));
                }

            }
            \Log::info('オークション終了後の処理が実行されました');

            return redirect()->back()->withErrors(['auction' => 'オークションは終了しました。']);
        }



        $currentTopBid = Bid::where('auction_id', $auction->id)
                            ->orderByDesc('amount')
                            ->first();

        $baseAmount = $currentTopBid->amount ?? $auction->suggested_price;
        $bidAmount = (int) $request->input('bid_amount');

        // 入札単位の判定（柔軟にカスタマイズ可能）
        if ($baseAmount >= 10000) {
            $minimumUnit = 1000;
        } elseif ($baseAmount >= 1000) {
            $minimumUnit = 100;
        } else {
            $minimumUnit = 10;
        }

        $diff = $bidAmount - $baseAmount;

        if ($diff < $minimumUnit || $diff % $minimumUnit !== 0) {
            return redirect()->back()->withErrors([
                'bid_amount' => "最低入札単位は {$minimumUnit} 円です。現在の価格（¥" . number_format($baseAmount) . "）より ¥{$minimumUnit} 以上増額し、かつ単位を守ってください。"
            ])->withInput();
        }

        // フォーマット等の基本バリデーション
        $request->validate([
            'bid_amount' => ['required', 'numeric', 'min:' . ($baseAmount + $minimumUnit)],
        ]);

        // 即決価格を超えたら支払いへ
        if ($bidAmount >= $auction->spot_price) {
            return redirect()->route('auction.payment', ['id' => $auction->id])
                             ->with('bid_amount', $bidAmount)
                             ->with('auction', $auction);
        }

        // 入札保存
        $bid = new Bid();
        $bid->auction_id = $auction->id;
        $bid->user_id = Auth::id();
        $bid->amount = $bidAmount;
        $bid->bid_time = now();
        $bid->save();

        return redirect()->route('home.auction.show', $auction->id)
                         ->with('success', '入札が完了しました。');
    }



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




}
