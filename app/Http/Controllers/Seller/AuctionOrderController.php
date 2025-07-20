<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AuctionOrder;

class AuctionOrderController extends Controller
{
    
    public function index()
    {
        $auction_orders = AuctionOrder::where('shop_id', auth()->user()->shop->id)->get();
        // dd($auction_orders);
        return view('sellers.auction.index', compact('auction_orders'));
    }

    public function markAccepted(AuctionOrder $auctionOrder)
    {

        $auctionOrder->delivery_status = '';
        $auctionOrder->delivery_status = 'accepted';
        $auctionOrder->save();

        $auctionOrder->update(['delivery_status'=>'accepted']);
        

        return redirect('/seller/shop_auction_orders')->withMessage('発送の受付をしました。');    
    }


    public function markDeliveryCom(AuctionOrder $auctionOrder, Request $request)
    {
        $auctionOrder->delivery_status = 1;
        $auctionOrder->save();

        // 関連オークションが存在する場合に delivery_status を更新
        if ($auctionOrder->auction) {
            $auctionOrder->auction->delivery_status = 1;
            $auctionOrder->auction->save();
        } else {
            \Log::warning('Auction not found for AuctionOrder ID: ' . $auctionOrder->id);
        }

        return redirect('/seller/shop_auction_orders')->withMessage('発送手配中です。');
    }


    public function markArranged(AuctionOrder $auctionOrder, Request $request)
    {
        $request->validate([
            'shipping_company' => 'required|string|max:255',
            'reception_number' => 'required|string|max:255',
        ]);
        $shipCom = $request->input('shipping_company');
        $shipInvoice = $request->input('reception_number');

        $auctionOrder->delivery_status ='';
        $auctionOrder->delivery_status = '2';

        $auctionOrder->shipping_company = $shipCom;
        $auctionOrder->reception_number = $shipInvoice;
        
        $auctionOrder->save();

        // 関連オークションが存在する場合に delivery_status を更新
        if ($auctionOrder->auction) {
            $auctionOrder->auction->delivery_status = 2;
            $auctionOrder->auction->shipping_company = $shipCom;
            $auctionOrder->auction->reception_number = $shipInvoice;
            $auctionOrder->auction->save();
        } else {
            \Log::warning('Auction not found for AuctionOrder ID: ' . $auctionOrder->id);
        }

        return redirect('/seller/shop_auction_orders')->withMessage('発送手配済みです (発送中)');    
    }

    public function markDelivered(AuctionOrder $auctionOrder, Request $request)
    {
        $auctionOrder->delivery_status ='';
        $auctionOrder->delivery_status = '3';
        $auctionOrder->save();

        // 関連オークションが存在する場合に delivery_status を更新
        if ($auctionOrder->auction) {
            $auctionOrder->auction->delivery_status = 3;
            $auctionOrder->auction->save();
        } else {
            \Log::warning('Auction not found for AuctionOrder ID: ' . $auctionOrder->id);
        }

        return redirect('/seller/shop_auction_orders')->withMessage('配達が完了しました');
    }

}
