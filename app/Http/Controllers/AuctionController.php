<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Auction;
use App\Models\Bid;
use Auth;

class AuctionController extends Controller
{
    public function auction_index(){

        $auction_items = Auction::where('status', 1)->get();
        $auction_photo_movies = Auction::where('status', 1)->get('cover_img1', 'cover_img2', 'cover_img3', 'cover_img4', 'cover_img5', 'cover_img6', 'cover_img7', 'movie');
        return view('auction.auction_index', ['auction_items' => $auction_items, 'auction_photo_movies' => $auction_photo_movies]);
    }

    public function auction_show(Request $request, $id){
        // dd($id);
        $auction_photo_movies = Auction::where('status', 1)->where('id', $id)->get(['cover_img1', 'cover_img2', 'cover_img3', 'cover_img4', 'cover_img5', 'cover_img6', 'cover_img7', 'movie']);
        // dd($auction_photo_movies);

        $auction_bid_items = Auction::find($id);
        // dd($auction_bid_items['name']);
        // dd($auction_bid_items->name);

        // 即決金額（Buy Now）が設定されている場合、即決のボタンが表示されます。
        $isBuyNow = $auction_bid_items->spot_price ? true : false;

        $current_time = now();

        $isAuctionNotStarted = $current_time < $auction_bid_items->start;

        $isAuctionEnded = $current_time > $auction_bid_items->end;

        $topBids = Bid::where('auction_id', $auction_bid_items->id)
                     ->where('bid_time', '<=', $auction_bid_items->end)
                     ->orderByDesc('amount')
                     ->take(3)
                     ->get(['id', 'amount', 'bid_time', 'user_id']);

         // ユーザー名を含む入札情報を取得
        foreach ($topBids as $bid) {
            $bid->user_name = $bid->user->name; // ユーザー名を追加

        }


        return view('auction.auction_show', ['auction_photo_movies' => $auction_photo_movies, 'auction_bid_items' => $auction_bid_items, 'id' => $id, 'topBids' => $topBids, 'isAuctionNotStarted' => $isAuctionNotStarted, 'isAuctionEnded' => $isAuctionEnded, 'isBuyNow' => $isBuyNow]);
    }

    public function storeBid(Request $request, Auction $auction)
    {
        // 入札金額のバリデーション
        $request->validate([
            'bid_amount' => 'required|numeric|min:' . $auction->suggested_price, // 最低入札価格をチェック
        ]);

        $auction = Auction::find($auction->id);
    
        // 入札額が即決金額以上の場合、即決価格を設定
        if ($request->bid_amount >= $auction->spot_price) {
            // 即決金額が入札された場合

            return redirect()->route('auction.payment', ['id' => $auction->id])
                ->with('bid_amount', $request->bid_amount)
                ->with('auction', $auction); // 決済画面に必要な情報を渡す
        }

        // 入札情報を保存
        $bid = new Bid();
        $bid->auction_id = $auction->id;
        $bid->user_id = Auth::id(); // ログインユーザーID
        $bid->amount = $request->bid_amount;
        $bid->bid_time = now(); // 入札時刻
        $bid->save();

        // 入札後にメッセージを設定
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





}
