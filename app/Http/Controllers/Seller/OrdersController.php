<?php

namespace App\Http\Controllers\Seller;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Response;

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
use App\Models\SubOrderItem;
use DB;
use Carbon\Carbon;

use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use ZipArchive;
use App\Exports\InvoiceExport;

class OrdersController extends Controller
{

    public function index(Request $request)
    {
        // 初期クエリ（ユーザーによって分岐）
        if (auth()->user()->id == 1) {
            $query = SubOrder::with('order');
            $coupons = ShopCoupon::get()->toArray();
            $campaigns = Campaign::get()->toArray();
        } else {
            $query = SubOrder::with('order')->where('seller_id', auth()->id());
            $coupons = ShopCoupon::where('shop_id', auth()->user()->shop->id)->get()->toArray();
            $campaigns = Campaign::where('shop_id', auth()->user()->shop->id)->get()->toArray();
        }

        // 🔍 検索処理
        if ($request->filled('search')) {
            $search = $request->input('search');

            $query->where(function ($q) use ($search) {
                // order テーブルに対する検索
                $q->whereHas('order', function ($q2) use ($search) {
                    $q2->where('shipping_fullname', 'like', "%{$search}%")
                        ->orWhere('shipping_phone', 'like', "%{$search}%")
                        ->orWhere('shipping_address', 'like', "%{$search}%")
                        ->orWhere('shipping_zipcode', 'like', "%{$search}%");
                })
                // sub_orders テーブルの status に対する検索
                ->orWhere('status', 'like', "%{$search}%");
            });
        }


        // 🔽 ソート処理
        $sortField = $request->get('sort', 'id');
        $direction = $request->get('direction', 'desc');
        $allowedSorts = ['order_number', 'id', 'status', 'shipping_fullname'];

        if (!in_array($sortField, $allowedSorts)) {
            $sortField = 'id';
        }

        if (in_array($sortField, ['order_number', 'shipping_fullname'])) {
            $query->join('orders', 'sub_orders.order_id', '=', 'orders.id')
                  ->orderBy("orders.{$sortField}", $direction)
                  ->select('sub_orders.*');
        } else {
            $query->orderBy($sortField, $direction);
        }

        // 📦 ページネーション + パラメータ保持
        $orders = $query->paginate(8)->appends($request->all());

        $mail_part = Shop::where('user_id', auth()->id())->first();
        // dd($mail_part);




        return view('sellers.orders.index', compact('mail_part', 'orders', 'coupons', 'campaigns'));
    }

    public function show(SubOrder $order)
    {   
        if(auth()->user()->id == 1){
            $items = $order->items;
            // dd($items);

            return view('sellers.orders.show', compact('items'));

        }else{
            $items = $order->items;

            $shopMane = auth()->user()->shop->id;
            // dd($shopMane);

            // dd($items);
            return view('sellers.orders.show', compact('items', 'shopMane'));
        }
        
        
    }

    // PostAjaxController.php
    public function fetch(Request $request)
    {
        $querySubOrder = SubOrder::query();

        if ($request->filled('search')) {
            $querySubOrder->where('title', 'like', '%' . $request->search . '%')
                  ->orWhere('content', 'like', '%' . $request->search . '%');
        }

        $sortField = $request->get('sort', 'created_at');
        $sortDirection = $request->get('direction', 'desc');

        // ★ソート対応（安全なカラムのみ許可）
        $allowedSorts = ['title', 'created_at'];
        if (!in_array($sortField, $allowedSorts)) {
            $sortField = 'created_at';
        }

        $partSubOrder = $querySubOrder->orderBy($sortField, $sortDirection)->paginate(10);

        return view('order.suborder.partials', compact('partSubOrder'))->render();
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

        $user = User::where('id', $data['user_id'])->first();

        $mailDisplay = Mails::where('shop_id', auth()->id())
            ->where('user_id', $data['user_id'])
            ->where(function ($query) {
                $query->where('template', 'template1')
                      ->orWhere('template', 'template3');
            })->first();
            
        // dd($mailDisplay);

        // 履歴が存在する場合のみ、排他チェックを実行
        if ($mailDisplay) {
            if (($mailDisplay->template == 'template1') && ($data['template'] == 'template3')) {
                return redirect('/seller/orders')->withMessage('クーポンを送った方には、このサイトからレビューを依頼できません!!');
            }

            if (($mailDisplay->template == 'template3') && ($data['template'] == 'template1')) {
                return redirect('/seller/orders')->withMessage('レビューを依頼した方には、このサイトからクーポンを送ることはできません!!');
            }
        }

        // 初回でも送信されるようにここで保存処理
        $mail = new Mails;
        $mail->user_id = $data['user_id'];
        $mail->shop_id = $data['shop_id'];
        $mail->mail = $user->email;
        $mail->template = $data['template'];
        $mail->coupon_id = $data['coupon_id'] ?? " ";
        $mail->campaign_id = $data['campaign_id'] ?? " ";
        $mail->order_number = $data['order_number'] ?? " ";
        $mail->order_coupon = $data['order_coupon'] ?? " ";
        $mail->save();

        return redirect('/seller/orders')->withMessage('Mail sent!!');
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

        $mails_histories = Mails::with('forMailCoupon')->where('shop_id', auth()->id())->latest()->paginate(20);

        return view('sellers.mails.shop_mails_history', compact(['mails_histories']));

    }

    public function invoice(Request $request)
    {

        // 必要なリレーションをまとめて取得
        $subOrders = SubOrder::where('seller_id', auth()->id())->
        with([
            'invoiceUser',
            'invoiceSubOrder_item',
            'invoiceArrivalReport'
        ])->orderByDesc('id')->get();

        // dd($subOrders->first()->coupon_code);

        // $subOrders の先頭1件の全体構造確認
        // dd($subOrders->first()->invoiceSubOrder_item);

        $sales = $subOrders->map(function ($order) {

        $purchase_date = Carbon::parse($order->created_at);

        $confirmedDate = $order->invoiceArrivalReport ? Carbon::parse($order->invoiceArrivalReport->confirmed_at) : null;

        $payoutDate = $order->transferred_at ? Carbon::parse($order->transferred_at) : null;

                        // 各商品の情報を取得
        $shop_parts = Shop::where('user_id', $order->invoiceUser->id)->first();
        // dd($order);
        // dd($product_parts);


        // $sub_order_item_parts = SubOrderItem::where('sub_order_id', $order->id)->first();

        // $product_parts = Product::where('id', $sub_order_item_parts->product_id)->first();

            // 注文の各商品ごとに1行ずつ作る
            return $order->invoiceSubOrder_item->map(function ($item) use ($order, $purchase_date, $confirmedDate, $payoutDate, $shop_parts) {

                // 商品情報をリレーションなしで取得
                $product = Product::where('id', $item->product_id)->first();
                $shippingFee = $product->shipping_fee ?? 0;

                return (object)[
                    'seller_id' => $order->seller_id,
                    'purchase_date' => $purchase_date,
                    'confirmed_at' => $confirmedDate,
                    'pay_transfer' => $payoutDate,
                    'order_number' => $order->id,
                    'seller_name' => $shop_parts->name ?? '',
                    'seller_registration_number' => $shop_parts->invoice_number ?? 'なし',
                    'product_name' => $item->product->name ?? '不明な商品',
                    'coupon_code' => $order->coupon_code ?? '不明なクーポン',
                    'quantity' => $item->quantity,
                    'shipping_fee' => $shippingFee,
                    'unit_price' => $item->price,
                    'tax_rate' => '10',
                    'tax_amount' => floor(($item->price*$item->quantity)*0.1),
                    'shippinng_fee_tax_amount' => floor(($shippingFee*$item->quantity)*0.1),
                    'total_amount' => floor(($item->price+$shippingFee)*1.1),
                    'tax_category' => '課税',
                ];
            });
        })->flatten(1);

            // CSV出力
        if ($request->format === 'csv') {

            $headers = [
                '購入日', '取引日(到着確認日)', '入金日(手数料以外)', '注文番号', '出品者名称', '出品者登録番号',
                '商品名', '数量', '単価(税抜)', '税率(%)', '消費税額(商品)', '税込金額', '単価(税抜)割引対象', '税率(%)', '消費税額', '税込金額', '配送料(税抜)', '税率(%)', '消費税額(配送料)', '税込金額', '税込金額合計', '消費税合計', '税区分'
            ];

            $callback = function () use ($sales, $headers) {
                $file = fopen('php://output', 'w');
                mb_convert_variables('SJIS-win', 'UTF-8', $headers);
                fputcsv($file, $headers);

                foreach ($sales as $row) {

                    $original_price = $row->unit_price;
                    $shipFee = $row->shipping_fee;
                    $shipping_fee_tax = $shipFee *0.1;

                    $shop_coupon = ShopCoupon::where('code', $row->coupon_code)->first();

                    $shop_campaign_pre = Shop::where('user_id', $row->seller_id)->first();

                    //$row->purchase_date 購入日で期間を確認する

                    $campaign_set = Campaign::where('shop_id', $shop_campaign_pre->id)->where('start_date', '<=', $row->purchase_date)
                        ->where('end_date', '>=', $row->purchase_date)
                        ->orderByDesc('dicount_rate1')
                        ->first();

                    $campaign_set_price = null; // 初期化しておくと安全
                    if($campaign_set){
                        if($row->quantity >= 2){
                            $campaign_set_price = $row->unit_price - ($row->unit_price * $campaign_set->dicount_rate1);
                            $campaign_set_price_tax = $campaign_set_price * 0.1;
                            $campaign_set_price_remove_total = $row->unit_price * ($row->quantity - 1);
                            $campaign_total = $campaign_set_price+($row->unit_price*($row->quantity-1));
                        }                        
                    }

                    $coupon_set = null; // 初期化しておくと安全
                    if(isset($shop_coupon)){
                        if($row->quantity >= 2){
                            $coupon_set = $row->unit_price + $shop_coupon->value;
                            $coupon_set_tax = $coupon_set * 0.1;
                            $coupon_set_remove_total = $row->unit_price * ($row->quantity - 1);
                        }                        
                    }

                    $prices = array_filter([
                        $original_price,
                        $campaign_set_price,
                        $coupon_set
                    ], fn($v) => $v !== null);

                    $lowest_price = !empty($prices) ? min($prices) : null;

                    $campaign_total = floor((
                        $campaign_set_price +
                        $row->unit_price * ($row->quantity - 1) +
                        ($shipFee * $row->quantity)
                    ) * 1.1);

                    $couponTotal = floor((
                        $coupon_set +
                        $row->unit_price * ($row->quantity - 1) +
                        ($shipFee * $row->quantity)
                    ) * 1.1);

                    $data = [
                        optional($row->purchase_date)->format('Y-m-d'),
                        optional($row->confirmed_at)->format('Y-m-d'),
                        optional($row->pay_transfer)->format('Y-m-d'),
                        $row->order_number,
                        $row->seller_name,
                        $row->seller_registration_number,
                        $row->product_name,
                        $row->quantity,
                        $row->unit_price,
                        $row->tax_rate,
                        ($row->unit_price*($row->quantity -1))*0.1,
                        ($row->unit_price*($row->quantity -1))*1.1,
                        $lowest_price,
                        $row->tax_rate,
                        $lowest_price*0.1,
                        $lowest_price*1.1,
                        $shipFee,
                        $row->tax_rate,
                        $shipFee*0.1,
                        $shipFee*1.1,
                        ($lowest_price + $shipFee*$row->quantity + $row->unit_price*($row->quantity -1))*1.1,
                        ($lowest_price + $shipFee*$row->quantity + $row->unit_price*($row->quantity -1))*0.1,
                        $row->tax_category,
                    ];
                    mb_convert_variables('SJIS-win', 'UTF-8', $data);
                    fputcsv($file, $data);
                }
                fclose($file);
            };

            return Response::stream($callback, 200, [
                "Content-Type" => "text/csv",
                "Content-Disposition" => "attachment; filename=sales.csv",
            ]);
        }

        return view('sellers.sales.sales_index', compact('subOrders', 'sales', 'headers'));
    }


    public function invoice2(Request $request)
    {

        $headers = [
            '購入日', '取引日(到着確認日)', '入金日(手数料以外)', '注文番号', '出品者名称', '出品者登録番号',
            '商品名', '数量', '単価(税抜)', '税率(%)', '消費税額(商品)', '税込金額', '単価(税抜)割引対象', '税率(%)', '消費税額', '税込金額', '配送料(税抜)', '税率(%)', '消費税額(配送料)', '税込金額', '税込金額合計', '消費税合計', '税区分'
        ];

        // 必要なリレーションをまとめて取得
        $subOrders = SubOrder::where('seller_id', auth()->id())->
        with([
            'invoiceUser',
            'invoiceSubOrder_item',
            'invoiceArrivalReport'
        ])->orderByDesc('id')->get();

        // dd($subOrders->first()->coupon_code);

        // $subOrders の先頭1件の全体構造確認
        // dd($subOrders->first()->invoiceSubOrder_item);

        $sales = $subOrders->map(function ($order) {

        $purchase_date = Carbon::parse($order->created_at);

        $confirmedDate = $order->invoiceArrivalReport ? Carbon::parse($order->invoiceArrivalReport->confirmed_at) : null;

        $payoutDate = $order->transferred_at ? Carbon::parse($order->transferred_at) : null;

                        // 各商品の情報を取得
        $shop_parts = Shop::where('user_id', $order->invoiceUser->id)->first();
        // dd($order);
        // dd($product_parts);


        // $sub_order_item_parts = SubOrderItem::where('sub_order_id', $order->id)->first();

        // $product_parts = Product::where('id', $sub_order_item_parts->product_id)->first();

            // 注文の各商品ごとに1行ずつ作る
            return $order->invoiceSubOrder_item->map(function ($item) use ($order, $purchase_date, $confirmedDate, $payoutDate, $shop_parts) {

                // 商品情報をリレーションなしで取得
                $product = Product::where('id', $item->product_id)->first();
                $shippingFee = $product->shipping_fee ?? 0;

                return (object)[
                    'seller_id' => $order->seller_id,
                    'purchase_date' => $purchase_date,
                    'confirmed_at' => $confirmedDate,
                    'pay_transfer' => $payoutDate,
                    'order_number' => $order->id,
                    'seller_name' => $shop_parts->name ?? '',
                    'seller_registration_number' => $shop_parts->invoice_number ?? 'なし',
                    'product_name' => $item->product->name ?? '不明な商品',
                    'coupon_code' => $order->coupon_code ?? '不明なクーポン',
                    'quantity' => $item->quantity,
                    'shipping_fee' => $shippingFee,
                    'unit_price' => $item->price,
                    'tax_rate' => '10',
                    'tax_amount' => floor(($item->price*$item->quantity)*0.1),
                    'shippinng_fee_tax_amount' => floor(($shippingFee*$item->quantity)*0.1),
                    'total_amount' => floor(($item->price+$shippingFee)*1.1),
                    'tax_category' => '課税',
                ];
            });
        })->flatten(1);

        // CSV出力
        if ($request->format === 'csv') {

            $filenameCsv = 'sales.csv';
            $filenameHtml = 'readme.html';
            $zipFilename = 'export_files.zip';

            // --- CSVをストレージに保存 ---
            $tmpDir = storage_path('app/tmp');
            // ディレクトリがなければ作成
            if (!file_exists($tmpDir)) {
                mkdir($tmpDir, 0777, true);
            }

            $csvPath = $tmpDir . '/sales.csv';

            $file = fopen($csvPath, 'w');

            $headers = [
                '購入日', '取引日(到着確認日)', '入金日(手数料以外)', '注文番号', '出品者名称', '出品者登録番号',
                '商品名', '数量', '単価(税抜)', '税率(%)', '消費税額(商品)', '税込金額', 
                '単価(税抜)割引対象', '税率(%)', '消費税額', '税込金額', '配送料(税抜)', 
                '税率(%)', '消費税額(配送料)', '税込金額', '税込金額合計', '消費税合計', '税区分'
            ];

            mb_convert_variables('SJIS-win','UTF-8',$headers);
            fputcsv($file, $headers);

            foreach ($sales as $row) {

                    $original_price = $row->unit_price;
                    $shipFee = $row->shipping_fee;
                    $shipping_fee_tax = $shipFee *0.1;

                    $shop_coupon = ShopCoupon::where('code', $row->coupon_code)->first();

                    $shop_campaign_pre = Shop::where('user_id', $row->seller_id)->first();

                    //$row->purchase_date 購入日で期間を確認する

                    $campaign_set = Campaign::where('shop_id', $shop_campaign_pre->id)->where('start_date', '<=', $row->purchase_date)
                        ->where('end_date', '>=', $row->purchase_date)
                        ->orderByDesc('dicount_rate1')
                        ->first();

                    $campaign_set_price = null; // 初期化しておくと安全
                    if($campaign_set){
                        if($row->quantity >= 2){
                            $campaign_set_price = $row->unit_price - ($row->unit_price * $campaign_set->dicount_rate1);
                            $campaign_set_price_tax = $campaign_set_price * 0.1;
                            $campaign_set_price_remove_total = $row->unit_price * ($row->quantity - 1);
                            $campaign_total = $campaign_set_price+($row->unit_price*($row->quantity-1));
                        }                        
                    }

                    $coupon_set = null; // 初期化しておくと安全
                    if(isset($shop_coupon)){
                        if($row->quantity >= 2){
                            $coupon_set = $row->unit_price + $shop_coupon->value;
                            $coupon_set_tax = $coupon_set * 0.1;
                            $coupon_set_remove_total = $row->unit_price * ($row->quantity - 1);
                        }                        
                    }

                    $prices = array_filter([
                        $original_price,
                        $campaign_set_price,
                        $coupon_set
                    ], fn($v) => $v !== null);

                    $lowest_price = !empty($prices) ? min($prices) : null;

                    $campaign_total = floor((
                        $campaign_set_price +
                        $row->unit_price * ($row->quantity - 1) +
                        ($shipFee * $row->quantity)
                    ) * 1.1);

                    $couponTotal = floor((
                        $coupon_set +
                        $row->unit_price * ($row->quantity - 1) +
                        ($shipFee * $row->quantity)
                    ) * 1.1);

                    $data = [
                        optional($row->purchase_date)->format('Y-m-d'),
                        optional($row->confirmed_at)->format('Y-m-d'),
                        optional($row->pay_transfer)->format('Y-m-d'),
                        $row->order_number,
                        $row->seller_name,
                        $row->seller_registration_number,
                        $row->product_name,
                        $row->quantity,
                        $row->unit_price,
                        $row->tax_rate,
                        ($row->unit_price*($row->quantity -1))*0.1,
                        ($row->unit_price*($row->quantity -1))*1.1,
                        $lowest_price,
                        $row->tax_rate,
                        $lowest_price*0.1,
                        $lowest_price*1.1,
                        $shipFee,
                        $row->tax_rate,
                        $shipFee*0.1,
                        $shipFee*1.1,
                        ($lowest_price + $shipFee*$row->quantity + $row->unit_price*($row->quantity -1))*1.1,
                        ($lowest_price + $shipFee*$row->quantity + $row->unit_price*($row->quantity -1))*0.1,
                        $row->tax_category,
                    ];
                mb_convert_variables('SJIS-win','UTF-8',$data);
                fputcsv($file, $data);
            }
            fclose($file);

            // --- HTML解説を生成 ---
            $htmlContent = view('sellers.sales.csv_guide', compact('headers'))->render();
            $htmlPath = storage_path("app/tmp/{$filenameHtml}");
            file_put_contents($htmlPath, $htmlContent);

            // --- ZIPにまとめる ---
            $zipPath = storage_path("app/tmp/{$zipFilename}");
            $zip = new ZipArchive;
            if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
                $zip->addFile($csvPath, $filenameCsv);
                $zip->addFile($htmlPath, $filenameHtml);
                $zip->close();
            }

            // --- ダウンロード ---
            return response()->download($zipPath)->deleteFileAfterSend(true);
        }

        return view('sellers.sales.sales_index', compact('subOrders', 'sales' , 'headers'));
    }

    public function slip()
    {
        $headers = [
            '購入日', '取引日(到着確認日)', '入金日', '注文番号', '出品者名称', '出品者登録番号',
            '商品名', '数量', '単価(税抜)', '税率(%)', '消費税額', '税込金額', '配送料', '配送料消費税', '税込金額合計', '税区分'
        ];

        $subOrders = SubOrder::where('seller_id', auth()->id())
            ->with([
                'invoiceUser',
                'invoiceSubOrder_item',
                'invoiceArrivalReport'
            ])->orderByDesc('id')->get();

        $sales = $subOrders->map(function ($order) {
            $purchase_date = \Carbon\Carbon::parse($order->created_at);
            $confirmedDate = $order->invoiceArrivalReport ? \Carbon\Carbon::parse($order->invoiceArrivalReport->confirmed_at) : null;
            $payoutDate = $order->transferred_at ? \Carbon\Carbon::parse($order->transferred_at) : null;
            $shop_parts = Shop::where('user_id', $order->invoiceUser->id)->first();

            // 商品リスト + 合計計算
            $items = $order->invoiceSubOrder_item->map(function ($item) {

                $product = Product::find($item->product_id);
                $shippingFee = $product->shipping_fee ?? 0;
                $shippingFeeTotal = $shippingFee * $item->quantity;

                $subtotal = $item->price * $item->quantity;
                $tax_amount = floor($subtotal * 0.1);
                $shipping_tax = floor($shippingFee * 0.1);
                $total_amount = $subtotal + $tax_amount + $shippingFee + $shipping_tax;

                return (object)[
                    'product_name' => $product->name ?? '不明な商品',
                    'quantity' => $item->quantity,
                    'unit_price' => $item->price,
                    'tax_rate' => 10,
                    'tax_amount' => $tax_amount,
                    'shipping_fee' => $shippingFee,
                    'shipping_fee_tax_amount' => $shipping_tax * $item->quantity,
                    'total_amount' => $total_amount,
                    'tax_category' => '課税',
                ];
            });

            // 合計値を計算
            $order_totals = (object)[
                'quantity_total' => $items->sum('quantity'),
                'unit_price_total' => $items->sum(function ($i) {
                    return $i->unit_price * $i->quantity;
                }),
                'shipping_fee_total' => $items->sum(function ($i) {
                    return $i->shipping_fee * $i->quantity;
                }),
                'tax_total' => $items->sum('tax_amount'),
                'shipping_fee_tax_total' => $items->sum('shipping_fee_tax_amount'),
                'grand_total' => $items->sum('total_amount'),
            ];

            return (object)[
                'shop_location' => $shop_parts->location_1,
                'order_id' => $order->id,
                'purchase_date' => $purchase_date,
                'confirmed_at' => $confirmedDate,
                'payout_date' => $payoutDate,
                'seller_name' => $shop_parts->name ?? '',
                'seller_registration_number' => $shop_parts->invoice_number ?? 'なし',
                'items' => $items,
                'order_totals' => $order_totals,
            ];
        });

        return view('sellers.sales.sales_slip', compact('sales', 'headers'));
    }

    public function slip2()
    {
        $headers = [
            '購入日', '取引日(到着確認日)', '入金日(手数料以外)', '注文番号', '出品者名称', '出品者登録番号',
            '商品名', '数量', '単価(税抜)', '税率(%)', '消費税額(商品)', '税込金額',
            '配送料(税抜)', '消費税額(配送料)', '税込金額(配送料)', 
            '税込金額合計', '消費税合計', '税区分'
        ];

        $subOrders = SubOrder::where('seller_id', auth()->id())
            ->with(['invoiceUser', 'invoiceSubOrder_item', 'invoiceArrivalReport'])
            ->orderByDesc('id')
            ->get();

        $sales = $subOrders->map(function ($order) {
            $purchase_date = Carbon::parse($order->created_at);
            $confirmedDate = $order->invoiceArrivalReport ? Carbon::parse($order->invoiceArrivalReport->confirmed_at) : null;
            $payoutDate = $order->transferred_at ? Carbon::parse($order->transferred_at) : null;
            $shop_parts = Shop::where('user_id', $order->invoiceUser->id)->first();

            $items = $order->invoiceSubOrder_item->map(function ($item) {
                $product = Product::find($item->product_id);
                $shippingFee = $product->shipping_fee ?? 0;

                return [
                    'product_name' => $product->name ?? '不明な商品',
                    'quantity' => $item->quantity,
                    'unit_price' => $item->price,
                    'tax_rate' => 10,
                    'tax_amount' => floor($item->price * $item->quantity * 0.1),
                    'total_amount' => floor($item->price * $item->quantity * 1.1),
                    'shipping_fee' => $shippingFee,
                    'shipping_fee_tax_amount' => floor($shippingFee * $item->quantity * 0.1),
                    'shipping_fee_total' => floor($shippingFee * $item->quantity * 1.1),
                    'tax_category' => '課税',
                ];
            })->toArray(); // ← Collectionではなく配列に変換

            // 合計計算
            $total_quantity = collect($items)->sum('quantity');
            $total_price = collect($items)->sum(fn($i) => $i['unit_price'] * $i['quantity']);
            $total_tax = collect($items)->sum('tax_amount');

            $total_shipping = collect($items)->sum(fn($i) => $i['shipping_fee'] * $i['quantity']);

            $total_shipping_tax = collect($items)->sum('shipping_fee_tax_amount');

            return (object)[
                'order_id' => $order->id,
                'purchase_date' => $purchase_date,
                'confirmed_at' => $confirmedDate,
                'payout_date' => $payoutDate,
                'seller_name' => $shop_parts->name ?? '',
                'seller_registration_number' => $shop_parts->invoice_number ?? 'なし',
                'items' => $items,
                'totals' => [
                    'total_quantity' => $total_quantity,
                    'total_price' => $total_price,
                    'total_tax' => $total_tax + $total_shipping_tax,
                    'total_shipping' => $total_shipping,
                    'grand_total1' => $total_price,
                    'grand_total2' => $total_shipping,
                    'grand_total3' => $total_tax,
                    'grand_total4' => $total_shipping_tax,
                    'grand_total' => $total_price + $total_shipping + $total_tax + $total_shipping_tax,
                ],
            ];
        });
        // dd($sales);

        return view('sellers.sales.sales_slip2', compact('sales', 'headers'));
    }





    



}
