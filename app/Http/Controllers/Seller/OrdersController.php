<?php

namespace App\Http\Controllers\Seller;

use App\Models\Order;
use App\Models\Product;
use App\Models\SubOrder;
use App\Models\OrderItem;
use App\Models\User;
use App\Models\Mails;
use App\Models\ShopCoupon;
use App\Models\Shop;
use App\Models\Campaign;
use App\Models\Inquiries;
use App\Models\PriceHistory;
use App\Models\InventoryLog;
use DB;
use Carbon\Carbon;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class OrdersController extends Controller
{
    public function index(SubOrder $suborder)
    {
        // dd(auth()->user()->id);
        if(auth()->user()->id == 1){
            $orders = SubOrder::latest()->paginate(15);

            $coupons = ShopCoupon::get()->toArray();

            $campaigns = Campaign::get()->toArray();

        }else{
            $orders = SubOrder::where('seller_id', auth()->id())->latest()->paginate(15);

            $coupons = ShopCoupon::where('shop_id', auth()->user()->shop->id)->get()->toArray();

            $campaigns = Campaign::where('shop_id', auth()->user()->shop->id)->get()->toArray();
            // dd(isset($campaigns), isset($coupons), empty($campaigns), empty($coupons), is_null($campaigns), is_null($coupons));
            // dd($campaigns);
        }

        

        return view('sellers.orders.index', compact(['orders', 'coupons', 'campaigns']));

    }

    public function show(SubOrder $order)
    {   
        if(auth()->user()->id == 1){
            $items = $order->items;
            return view('sellers.orders.show', compact('items'));

        }else{
            $items = $order->items;

            $shopMane = auth()->user()->shop->id;
            // dd($shopMane);

            // dd($items);
            return view('sellers.orders.show', compact('items', 'shopMane'));
        }
        
        
    }

    public function markAccepted(SubOrder $suborder)
    {
        $suborder->payment_status = '';
        $suborder->payment_status = 'accepted';
        $suborder->save();

        
        $suborder->order()->update(['payment_status'=>'accepted']);
        

        return redirect('/seller/orders')->withMessage('Order marked Accepted　(Pending)');    
    }

    public function markDeliveryCom(SubOrder $suborder, Request $request)
    {
        $shipCom = $request->input('shipping_company');
        $shipInvoice = $request->input('invoice_number');

        $suborder->payment_status ='';
        $suborder->payment_status = "arranging delivery";
        
        $suborder->shipping_company = $shipCom;
        $suborder->invoice_number = $shipInvoice;
        $suborder->save();

        return redirect('/seller/orders')->withMessage('Order marked Arranging Delivery (Pending)');    
    }

    public function markArranged(SubOrder $suborder)
    {
        $suborder->payment_status ='';
        $suborder->status ='';
        $suborder->status = 'processing';
        $suborder->payment_status = "delivery arranged";
        
        $suborder->save();

        $suborder->order()->update(['status'=>'processing']);
        $suborder->order()->update(['payment_status'=>'delivery arranged']);
        

        return redirect('/seller/orders')->withMessage('Order marked Delivery Arranged (Processing)');    
    }

    public function markDelivered(SubOrder $suborder)
    {
        $suborder->payment_status ='';
        $suborder->status ='';
        $suborder->status = 'completed';
        $suborder->save();
        
        $suborder->order()->update(['status'=>'completed']);
        $suborder->order()->update(['payment_status'=>'mark as delivered']);
        

        return redirect('/seller/orders')->withMessage('Order marked Complete');
    }

    public function sendMail(Request $request)
    {
        $data = $request->all();
        // dd($data);
        // dd(empty($data['coupon_id']));

        $user = User::where('id', $data['user_id'])->first();
        // dd($user->email);

        $mailDisplay = Mails::where('shop_id', auth()->id())->where('user_id', $data['user_id'])->where('template', "template1")->orWhere('template', "template3")->first();

        // $mailDisplay = Mails::where('shop_id', auth()->id())->where('user_id', $data['user_id'])->first();

        // dd($mailDisplay->template, $data['template']);

        // dd(($mailDisplay->template == 'template3') && ($data['template'] === 'template1'));

        if(($mailDisplay->template == 'template1') && ($data['template'] == 'template3')){

            return redirect('/seller/orders')->withMessage('クーポンを送った方には、このサイトからレビューを依頼できません!!'); 

        }else if(($mailDisplay->template == 'template3') && ($data['template'] == 'template1')){

            return redirect('/seller/orders')->withMessage('レビューを依頼した方には、このサイトからクーポンを送ることはできません!!');

        }else{
        
            $mail = new Mails;
            $mail->user_id = $data['user_id'];
            $mail->shop_id = $data['shop_id'];
            $mail->mail = $user->email;
            $mail->template = $data['template'];
            if(empty($data['coupon_id'])){
                $mail->coupon_id = " "; 
            }else{
                $mail->coupon_id = $data['coupon_id'];    
            }
            if(empty($data['campaign_id'])){
                $mail->campaign_id = " "; 
            }else{
                $mail->campaign_id = $data['campaign_id'];    
            }
            
            $mail->save(); 

            return redirect('/seller/orders')->withMessage('Mail sent!!');  
        }

    }

    public function chartPage()
    {
        $sellerId = auth()->user()->id;
        // dd($sellerId);

        // ユーザー数の推移（例えば月ごとのユーザー数）
        $userCounts = User::select(DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'), DB::raw('count(*) as count'))->groupBy(DB::raw('DATE_FORMAT(created_at, "%Y-%m")'))->orderBy('month')->get();

        $userTotalCount = User::count();
        // dd($userTotalCount);


        // 月ごとのオーダー数
        $monthlySubOrderCounts = SubOrder::where('seller_id', $sellerId)
        ->select(DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'), DB::raw('count(*) as count'))
        ->groupBy(DB::raw('DATE_FORMAT(created_at, "%Y-%m")'))
        ->orderBy('month')
        ->get();

        // 曜日ごとのオーダー数
        $dailySubOrderCounts = SubOrder::where('seller_id', $sellerId)
        ->select(DB::raw('DAYOFWEEK(created_at) as day_of_week'), DB::raw('count(*) as count'))
        ->groupBy(DB::raw('DAYOFWEEK(created_at)'))
        ->orderBy('day_of_week')
        ->get();

        // 週ごとのオーダー数
        $weeklySubOrderCounts = SubOrder::where('seller_id', $sellerId)
        ->select(DB::raw('YEARWEEK(created_at) as week'), DB::raw('count(*) as count'))
        ->groupBy(DB::raw('YEARWEEK(created_at)'))
        ->orderBy('week')
        ->get();

        // 月ごとの売上金額
        $monthlySubOrderSalles = SubOrder::where('seller_id', $sellerId)
        ->select(DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'), DB::raw('SUM(grand_total) as total_sales'))
        ->groupBy(DB::raw('DATE_FORMAT(created_at, "%Y-%m")'))
        ->orderBy('month')
        ->get();

        // 曜日ごとの売上金額
        $dailySubOrderSalles = SubOrder::where('seller_id', $sellerId)
        ->select(DB::raw('DAYOFWEEK(created_at) as day_of_week'), DB::raw('SUM(grand_total) as total_sales'))
        ->groupBy(DB::raw('DAYOFWEEK(created_at)'))
        ->orderBy('day_of_week')
        ->get();

        // 週ごとの売上金額
        $weeklySubOrderSalles = SubOrder::where('seller_id', $sellerId)
        ->select(DB::raw('YEARWEEK(created_at) as week'), DB::raw('SUM(grand_total) as total_sales'))
        ->groupBy(DB::raw('YEARWEEK(created_at)'))
        ->orderBy('week')
        ->get();

        // dd($monthlySubOrderSalles, $dailySubOrderSalles, $weeklySubOrderSalles);

        // 月ごとのメール
        $monthlyMailsCounts = Mails::where('shop_id', $sellerId)
        ->select(DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'), DB::raw('count(*) as count'))
        ->groupBy(DB::raw('DATE_FORMAT(created_at, "%Y-%m")'))
        ->orderBy('month')
        ->get();

        // 週ごとのメール
        $weeklyMailsCounts = Mails::where('shop_id', $sellerId)
        ->select(DB::raw('YEARWEEK(created_at) as week'), DB::raw('count(*) as count'))
        ->groupBy(DB::raw('YEARWEEK(created_at)'))
        ->orderBy('week')
        ->get();

        // 曜日ごとのメール
        $dailyMailsCounts = Mails::where('shop_id', $sellerId)
        ->select(DB::raw('DAYOFWEEK(created_at) as day_of_week'), DB::raw('count(*) as count'))
        ->groupBy(DB::raw('DAYOFWEEK(created_at)'))
        ->orderBy('day_of_week')
        ->get();

        // dd(auth()->user()->forChart->id);

        $sellerId2 = auth()->user()->forChart->id;

        // 月ごとのお問合せ
        $monthlyInquiriesCounts = Inquiries::where('shop_id', $sellerId2)
        ->select(DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'), DB::raw('count(*) as count'))
        ->groupBy(DB::raw('DATE_FORMAT(created_at, "%Y-%m")'))
        ->orderBy('month')
        ->get();

        // dd($monthlyInquiriesCounts);

        // 週ごとのお問合せ
        $weeklyInquiriesCounts = Inquiries::where('shop_id', $sellerId2)
        ->select(DB::raw('YEARWEEK(created_at) as week'), DB::raw('count(*) as count'))
        ->groupBy(DB::raw('YEARWEEK(created_at)'))
        ->orderBy('week')
        ->get();

        // 曜日ごとのお問合せ
        $dailyInquiriesCounts = Inquiries::where('shop_id', $sellerId2)
        ->select(DB::raw('DAYOFWEEK(created_at) as day_of_week'), DB::raw('count(*) as count'))
        ->groupBy(DB::raw('DAYOFWEEK(created_at)'))
        ->orderBy('day_of_week')
        ->get();

        // 月ごとのクーポン
        $monthlyShopCouponCounts = ShopCoupon::where('shop_id', $sellerId2)
        ->select(DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'), DB::raw('count(*) as count'))
        ->groupBy(DB::raw('DATE_FORMAT(created_at, "%Y-%m")'))
        ->orderBy('month')
        ->get();

        // dd($monthlyShopCouponCounts);

        // 週ごとのクーポン
        $weeklyShopCouponCounts = ShopCoupon::where('shop_id', $sellerId2)
        ->select(DB::raw('YEARWEEK(created_at) as week'), DB::raw('count(*) as count'))
        ->groupBy(DB::raw('YEARWEEK(created_at)'))
        ->orderBy('week')
        ->get();

        // 曜日ごとのクーポン
        $dailyShopCouponCounts = ShopCoupon::where('shop_id', $sellerId2)
        ->select(DB::raw('DAYOFWEEK(created_at) as day_of_week'), DB::raw('count(*) as count'))
        ->groupBy(DB::raw('DAYOFWEEK(created_at)'))
        ->orderBy('day_of_week')
        ->get();

        // キャンペーン
        $campaigns = Campaign::where('shop_id', $sellerId2)->get();

        // 開始日と終了日の形式を`Y-m-d`にする
        $campaigns = $campaigns->map(function($campaign) {
            return [
                'name' => $campaign->name,
                'start_date' => $campaign->start_date->format('Y-m-d'),
                'end_date' => $campaign->end_date->format('Y-m-d'),
                'discount_rate' => $campaign->dicount_rate1,
            ];
        });

        // 価格遷移
        // $sellerId2 はショップの ID
        $priceHistory = PriceHistory::where('shop_id', $sellerId2)
            ->orderBy('updated_at', 'asc') // 価格変更日でソート
            ->get();

        // 商品ごとに価格履歴をグループ化（product_idごとに）
        $groupedPriceHistory = $priceHistory->groupBy('product_id');

        // 商品ごとのデータをまとめる
        $productData = [];

        foreach ($groupedPriceHistory as $productId => $histories) {
            // 商品の価格履歴データを準備
            $dates = $histories->pluck('updated_at')->toArray();
            $prices = $histories->pluck('price')->toArray();

            // 商品IDを使って商品名などの追加情報を取得（例：Productモデルを使う）
            $product = Product::find($productId); // 商品名を取得
            $productData[] = [
                'name' => $product->name,
                'dates' => $dates,
                'prices' => $prices,
            ];
        }
        // dd($productData);

        // Favorite

        // Fetch the products and their favorite scores over time
        $productFavorites = Product::where('shop_id', $sellerId2)->with(['user_favo' => function($query) {
            $query->orderBy('created_at', 'asc'); // Order by date
        }])->get();

        // $productFavorites = Product::where('shop_id', $sellerId2)->with('user_favo')->get();

        // dd($productFavorites);

        // グラフ用のデータを準備
        $productFavoriteschartData = [];
        foreach ($productFavorites as $product) {
            $dates = [];
            $scores = [];

            // 商品のお気に入りスコアを取得
            foreach ($product->user_favo as $favorite) {
                $dates[] = Carbon::parse($favorite->created_at)->format('Y-m-d H:i:s'); // 日付のフォーマット
                $scores[] = $favorite->wants; // スコア
            }

            $productFavoriteschartData[] = [
                'product_name' => $product->name,
                'dates' => $dates,
                'scores' => $scores
            ];
        }
        // dd($productFavoriteschartData);

        // Stock
        // 在庫データを日付順で取得
        $inventoryData = Product::where('shop_id', $sellerId2 )->with('inventoryLogs')->get();

        // dd($inventoryData);

        

        return view('sellers.charts.index', compact('userCounts', 'monthlySubOrderCounts', 'weeklySubOrderCounts', 'dailySubOrderCounts', 'monthlySubOrderSalles', 'dailySubOrderSalles', 'weeklySubOrderSalles', 'userTotalCount', 'monthlyMailsCounts', 'weeklyMailsCounts', 'dailyMailsCounts', 'monthlyInquiriesCounts', 'weeklyInquiriesCounts', 'dailyInquiriesCounts', 'monthlyShopCouponCounts', 'weeklyShopCouponCounts', 'dailyShopCouponCounts', 'campaigns', 'productData', 'productFavoriteschartData', 'inventoryData'));

    }

    public function shopMailHistory()
    {

        $mails_histories = Mails::where('shop_id', auth()->id())->latest()->paginate(20);

        return view('sellers.mails.shop_mails_history', compact(['mails_histories']));

    }

}