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
use App\Models\TaxRate;
use App\Models\Commition;
use App\Models\FinalOrder;
use DB;
use Carbon\Carbon;

use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use ZipArchive;
use App\Exports\InvoiceExport;
use Symfony\Component\HttpFoundation\StreamedResponse;

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
        $items = $order->items;

        $suborder = $order; 

        foreach ($items as $item) {
            // dd($item);
            $quantity   = $item->pivot->quantity;
            $unitPrice  = $item->pivot->price;
            $shipping   = $item->pivot->shipping_fee ?? 0;

            // 税抜価格（商品 + 送料）
            $originalPriceWithShipping = $unitPrice + $shipping;

            // 割引後価格（キャンペーン/クーポン適用）
            $discountedUnitPrice = $item->pivot->discounted_price ?? $unitPrice;
            $lowestUnitPrice = $discountedUnitPrice + $shipping;
            // dd($discountedUnitPrice);
            // 適用ラベル
            if (isset($item->pivot->campaign_id) && $item->pivot->campaign_id) {
                $appliedLabel = 'キャンペーン適用';
            } elseif (isset($item->pivot->coupon_id) && $item->pivot->coupon_id) {
                $appliedLabel = 'クーポン適用';
            } else {
                $appliedLabel = 'なし';
            }

            // 小計（1個だけ割引、それ以外は通常価格）
            if ($quantity > 1 && $lowestUnitPrice < $originalPriceWithShipping) {
                $finalLineTotal = $lowestUnitPrice + ($originalPriceWithShipping * ($quantity - 1));
            } else {
                $finalLineTotal = $lowestUnitPrice * $quantity;
            }

            // 課税判定（インボイス番号があれば課税）
            $isTaxable = !empty($item->pivot->invoice_number ?? null);

            $taxRate = TaxRate::current()?->rate; // 消費税率10%

            $tax = $isTaxable ? (int)($finalLineTotal * $taxRate) : 0;

            // 手数料計算（例）
            // $feeRate  = 0.05; // 5% 手数料
            $feeRate = Commition::current()?->rate ?? 0; // 10% 手数料
            $feeFixed = 0;
            $fee = (int)($originalPriceWithShipping * $quantity * $feeRate + $feeFixed);

            // 出品者受取額
            $sellerReceive = $finalLineTotal - $fee - $tax;

            $item->calc = [
                'originalPriceWithShipping' => $originalPriceWithShipping,
                'lowestUnitPrice'           => $lowestUnitPrice,
                'appliedLabel'              => $appliedLabel,
                'finalLineTotal'            => $finalLineTotal,
                'tax'                       => $tax,
                'fee'                       => $fee,
                'sellerReceive'             => $sellerReceive,
            ];
        }

        if(auth()->user()->id == 1){
            return view('sellers.orders.show', compact('items'));
        } else {
            $shopMane = auth()->user()->shop->id;
            return view('sellers.orders.show', compact('items', 'shopMane', 'suborder'));
        }
    }

    public function exportSubOrdersCsv(Request $request): StreamedResponse
    {
        $user = auth()->user();

        // 課税判定（インボイス番号があれば課税）
        $isTaxable = !empty($user->shop->invoice_number ?? null);

        $taxRate = TaxRate::current()?->rate;

        /*
        |--------------------------------------------------------------------------
        | ① 期間指定
        |--------------------------------------------------------------------------
        */
        $startDate = $request->start_date
            ? Carbon::parse($request->start_date)->startOfDay()
            : null;

        $endDate = $request->end_date
            ? Carbon::parse($request->end_date)->endOfDay()
            : null;

        /*
        |--------------------------------------------------------------------------
        | ② データ取得（店舗＋期間）
        |--------------------------------------------------------------------------
        */
        $query = SubOrder::with(['items'])
            ->where('seller_id', $user->id);

        if ($startDate && $endDate) {
            $query->whereBetween('created_at', [$startDate, $endDate]);
        } elseif ($startDate) {
            $query->where('created_at', '>=', $startDate);
        } elseif ($endDate) {
            $query->where('created_at', '<=', $endDate);
        }

        $subOrders = $query->get();

        /*
        |--------------------------------------------------------------------------
        | ③ N+1排除（ID収集→一括取得）
        |--------------------------------------------------------------------------
        */
        $campaignIds = [];
        $couponIds   = [];

        foreach ($subOrders as $subOrder) {
            foreach ($subOrder->items as $item) {
                if ($item->pivot->campaign_id) {
                    $campaignIds[] = $item->pivot->campaign_id;
                }
                if ($item->pivot->coupon_id) {
                    $couponIds[] = $item->pivot->coupon_id;
                }
            }
        }

        $campaigns = Campaign::whereIn('id', array_unique($campaignIds))
            ->get()->keyBy('id');

        $coupons = ShopCoupon::whereIn('id', array_unique($couponIds))
            ->get()->keyBy('id');

        $feeRate = Commition::first()->rate ?? 0;

        /*
        |--------------------------------------------------------------------------
        | ④ CSV出力
        |--------------------------------------------------------------------------
        */
        return response()->streamDownload(function () use (
            $subOrders,
            $campaigns,
            $coupons,
            $feeRate,
            $isTaxable,
            $taxRate
        ) {

            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF"); // BOM

            /*
            |--------------------------------------------------------------------------
            | ヘッダー分離
            |--------------------------------------------------------------------------
            */
            if ($isTaxable) {

                fputcsv($handle, [
                    '商品ID',
                    '商品名',
                    '数量',
                    '商品小計',
                    '商品消費税',
                    '送料税抜',
                    '送料消費税',
                    '合計消費税',
                    '割引額',
                    '手数料',
                    '最終税込金額',
                    '適用',
                    '注文日'
                ]);

            } else {

                fputcsv($handle, [
                    '商品ID',
                    '商品名',
                    '数量',
                    '商品小計',
                    '送料',
                    '割引額',
                    '手数料',
                    '最終金額',
                    '適用',
                    '注文日'
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | データ処理
            |--------------------------------------------------------------------------
            */
            foreach ($subOrders as $subOrder) {

                foreach ($subOrder->items as $item) {

                    $quantity  = (int) $item->pivot->quantity;
                    $unitPrice = (float) $item->pivot->price;
                    $shipping  = (float) $item->shipping_fee;

                    $totalOriginal = $unitPrice * $quantity;
                    $totalShipping = $shipping * $quantity;
                    $fee = floor($totalOriginal * $feeRate);

                    /*
                    |--------------------------------------------------------------------------
                    | 割引候補計算
                    |--------------------------------------------------------------------------
                    */
                    $campaignUnit = null;
                    $couponUnit   = null;

                    if ($item->pivot->campaign_id) {
                        $campaign = $campaigns->get($item->pivot->campaign_id);
                        if ($campaign) {
                            $campaignUnit = floor(
                                $unitPrice * (1 - $campaign->dicount_rate1)
                            );
                        }
                    }

                    if ($item->pivot->coupon_id) {
                        $coupon = $coupons->get($item->pivot->coupon_id);
                        if ($coupon) {
                            $couponUnit = max(0, $unitPrice + $coupon->value);
                        }
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | 最安値1個のみ適用
                    |--------------------------------------------------------------------------
                    */
                    $bestUnitPrice = $unitPrice;
                    $appliedLabel  = 'なし';

                    $candidates = [];

                    if (!is_null($campaignUnit)) {
                        $candidates['キャンペーン'] = $campaignUnit;
                    }

                    if (!is_null($couponUnit)) {
                        $candidates['クーポン'] = $couponUnit;
                    }

                    if (!empty($candidates)) {
                        $minPrice = min($candidates);
                        $appliedLabel = array_search($minPrice, $candidates);
                        $bestUnitPrice = $minPrice;
                    }

                    if ($quantity >= 1 && $bestUnitPrice < $unitPrice) {
                        $subtotal =
                            $bestUnitPrice
                            + ($unitPrice * ($quantity - 1));
                    } else {
                        $subtotal = $unitPrice * $quantity;
                    }

                    $discountAmount = $totalOriginal - $subtotal;

                    /*
                    |--------------------------------------------------------------------------
                    | 非課税業者
                    |--------------------------------------------------------------------------
                    */
                    if (!$isTaxable) {

                        $finalTotal = $subtotal + $totalShipping;

                        fputcsv($handle, [
                            $item->id,
                            $item->name,
                            $quantity,
                            $subtotal,
                            $totalShipping,
                            $discountAmount,
                            $fee,
                            $finalTotal,
                            $appliedLabel,
                            $subOrder->created_at->format('Y-m-d'),
                        ]);

                    } else {

                        /*
                        |--------------------------------------------------------------------------
                        | 課税業者
                        |--------------------------------------------------------------------------
                        */
                        $productTax  = floor($subtotal * $taxRate);
                        $shippingTax = floor($totalShipping * $taxRate);
                        $totalTax    = $productTax + $shippingTax;

                        $finalTotal = $subtotal
                                    + $totalShipping
                                    + $totalTax;

                        fputcsv($handle, [
                            $item->id,
                            $item->name,
                            $quantity,
                            $subtotal,
                            $productTax,
                            $totalShipping,
                            $shippingTax,
                            $totalTax,
                            $discountAmount,
                            $fee,
                            $finalTotal,
                            $appliedLabel,
                            $subOrder->created_at->format('Y-m-d'),
                        ]);
                    }
                }
            }

            fclose($handle);

        }, 'suborders_' . now()->format('Ymd_His') . '.csv');
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

        $subOrder = SubOrder::where('id', $data['order_id'])->first();
        // dd($subOrder->created_at);

        $product_pre = SubOrder::with('invoiceSubOrder_item')->where('id', $data['order_id'])->first();
        // dd($product_pre);



        $shops = Shop::where('user_id', $data['shop_id'])->first();

        $campaigns = Campaign::where('shop_id', $shops->id)->where('start_date', '<=', $subOrder->created_at)->where('end_date', '>=', $subOrder->created_at)->orderByDesc('dicount_rate1')->first();


        // dd($campaigns->id);

        // coupon_code が存在する場合
        $coupons = [];
        if (!empty($subOrder->coupon_code)) {
            // カンマで分割
            $codes = explode(',', $subOrder->coupon_code);

            // それぞれのコードで ShopCoupon を検索
            foreach ($codes as $code) {
                $coupon = ShopCoupon::where('code', trim($code))->first();
                if ($coupon) {
                    $coupons[] = $coupon->id;
                }
            }
        }
        // dd($coupons);

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

        // invoiceSubOrder_item から product_id だけ抜き出す
        $productIds = $product_pre->invoiceSubOrder_item->pluck('product_id')->toArray();

        // 単一キャンペーンIDを配列に
        $campaignIds = [];
        if (!empty($campaigns) && !empty($campaigns->id)) {
            $campaignIds[] = $campaigns->id;
        }

        $subOrderItems = SubOrder::with(['invoiceSubOrder_item.product'])->where('id', $data['order_id'])->first();

        // dd($subOrderItems);

        // 初回でも送信されるようにここで保存処理
        $mail = new Mails;
        $mail->user_id = $data['user_id'];
        $mail->shop_id = $data['shop_id'];
        $mail->mail = $user->email;
        $mail->template = $data['template'];
        $mail->purpose = $data['purpose'];
        
        // カンマ区切り文字列に変換
        $mail->product_id = implode(',', $productIds);  

        // 複数クーポンIDをカンマ区切り文字列に変換
        $mail->coupon_id = !empty($coupons) ? implode(',', $coupons) : null;

        // カンマ区切りにして保存
        $mail->campaign_id = implode(',', $campaignIds) ?: " ";
        $mail->order_number = $data['order_number'] ?? " ";
        $mail->sub_order_id = $data['order_id'] ?? " ";

        // dd($mail);

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

        // 必要なリレーションをまとめて取得
        $finalOrders = FinalOrder::where('shop_id', auth()->user()->shop->id)->orderByDesc('id')->get();

        // dd($subOrders->first()->coupon_code);
        // dd($finalOrders);

        // $subOrders の先頭1件の全体構造確認
        // dd($subOrders->first()->invoiceSubOrder_item);

        $sales = $subOrders->map(function ($order) {

        $purchase_date = Carbon::parse($order->created_at);

        $confirmedDate = $order->invoiceArrivalReport ? Carbon::parse($order->invoiceArrivalReport->confirmed_at) : null;

        $payoutDate = $order->transferred_at ? Carbon::parse($order->transferred_at) : null;

                        // 各商品の情報を取得
        $shop_parts = Shop::where('user_id', $order->invoiceUser->id)->first();
        // dd($order);
        // dd($shop_parts->invoice_number);

        $tax_rate = TaxRate::current()?->rate;
        // dd($tax_rate);

        // $sub_order_item_parts = SubOrderItem::where('sub_order_id', $order->id)->first();

        // $product_parts = Product::where('id', $sub_order_item_parts->product_id)->first();

            // 注文の各商品ごとに1行ずつ作る
            return $order->invoiceSubOrder_item->map(function ($item) use ($order, $purchase_date, $confirmedDate, $payoutDate, $shop_parts, $tax_rate) {

                // 商品情報をリレーションなしで取得
                $product = Product::where('id', $item->product_id)->first();
                $shippingFee = $product->shipping_fee ?? 0;
                $tax_category = '免税業者';
                if($shop_parts->invoice_number)
                {
                    $tax_category = '課税業者';

                    return (object)[
                        'seller_id' => $order->seller_id,
                        'purchase_date' => $purchase_date,
                        'confirmed_at' => $confirmedDate,
                        'pay_transfer' => $payoutDate,
                        'order_number' => $order->id,
                        'seller_name' => $shop_parts->name ?? '',
                        'seller_registration_number' => $shop_parts->invoice_number ?? 'なし',
                        'product_id' => $item->product->id ?? '不明な商品ID',
                        'product_name' => $item->product->name ?? '不明な商品',
                        'coupon_code' => $order->coupon_code ?? '不明なクーポン',
                        'quantity' => $item->quantity,
                        'shipping_fee' => $shippingFee,
                        'unit_price' => $item->price,
                        'tax_rate' => '10',//表示上
                        'tax_amount' => floor(($item->price*$item->quantity)*$tax_rate),
                        'shippinng_fee_tax_amount' => floor(($shippingFee*$item->quantity)*$tax_rate),
                        'total_amount' => floor(($item->price+$shippingFee)*($tax_rate+1)),
                        'tax_category' => $tax_category,
                    ];
                }else
                    {
                        return (object)[
                            'seller_id' => $order->seller_id,
                            'purchase_date' => $purchase_date,
                            'confirmed_at' => $confirmedDate,
                            'pay_transfer' => $payoutDate,
                            'order_number' => $order->id,
                            'seller_name' => $shop_parts->name ?? '',
                            'seller_registration_number' => $shop_parts->invoice_number ?? 'なし',
                            'product_id' => $item->product->id ?? '不明な商品ID',
                            'product_name' => $item->product->name ?? '不明な商品',
                            'coupon_code' => $order->coupon_code ?? '不明なクーポン',
                            'quantity' => $item->quantity,
                            'shipping_fee' => $shippingFee,
                            'unit_price' => $item->price,
                            'tax_rate' => 'なし',//表示上
                            'tax_amount' => floor(($item->price*$item->quantity)),
                            'shippinng_fee_tax_amount' => floor($shippingFee*$item->quantity),
                            'total_amount' => floor($item->price+$shippingFee),
                            'tax_category' => $tax_category,
                        ];    
                    }

                
            });
        })->flatten(1);
        // dd($subOrders);
        // dd($sales);

        // CSV出力
        if ($request->format === 'csv') {

            $filenameCsv  = 'sales.csv';
            $filenameHtml = 'readme.html';
            $zipFilename  = 'export_files.zip';

            // 一時ディレクトリ
            $tmpDir = storage_path('app/tmp');
            if (!file_exists($tmpDir)) mkdir($tmpDir, 0777, true);

            $csvPath = $tmpDir . '/sales.csv';
            $file = fopen($csvPath, 'w');

            // 税区分判定（免税/課税）
            $taxCategory = $sales->first()->tax_category ?? '免税業者';
            $tax_rate = TaxRate::current()?->rate ?? 0;

            // CSVヘッダー切替
            if($taxCategory === '免税業者'){
                $headers = [
                    '購入日','取引日','入金日','注文番号','出品者名称','出品者登録番号',
                    '商品名','数量','単価','割引後単価','適用ラベル',
                    '2個目以降単価','配送料','税込金額合計','税区分'
                ];
            } else {
                $headers = [
                    '購入日','取引日','入金日','注文番号','出品者名称','出品者登録番号',
                    '商品名','数量','単価(税抜)','税率(%)','割引後単価', '消費税額(1個目)','税込金額(1個目)',
                    '適用ラベル','2個目以降税抜合計','2個目以降消費税','2個目以降税込合計',
                    '配送料(税抜)','配送料消費税','配送料税込','税込金額合計','消費税合計','税区分'
                ];
            }

            mb_convert_variables('SJIS-win','UTF-8',$headers);
            fputcsv($file, $headers);

            foreach($sales as $row){
                $quantity = $row->quantity;
                $unit_price = $row->unit_price;
                $shipFee = $row->shipping_fee;

                // --- キャンペーン適用 ---
                $campaign_set_price = null;
                $shop_campaign = Campaign::where('shop_id', auth()->user()->shop->id)
                    ->where('start_date','<=',$row->purchase_date)
                    ->where('end_date','>=',$row->purchase_date)
                    ->orderByDesc('dicount_rate1')->first();
                if($shop_campaign && $quantity >= 2){
                    $campaign_set_price = floor($unit_price * (1 - $shop_campaign->dicount_rate1));
                }

                // --- クーポン適用 ---
                $coupon_set_price = null;

                if (!empty($row->coupon_code)) {
                    // 複数のクーポンコードを配列化
                    $couponCodes = explode(',', $row->coupon_code);

                    // クーポンをまとめて取得
                    $shop_coupons = ShopCoupon::whereIn('code', $couponCodes)->get();

                    foreach ($shop_coupons as $shop_coupon) {
                        // 対象商品か確認
                        if ($row->product_id == $shop_coupon->product_id) {
                            // クーポンは最初の1個だけ適用
                            // ※値引きなら -、値上げクーポンなら + にしてください
                            $coupon_set_price = max(0, $unit_price + $shop_coupon->value);
                            break; // 最初に見つかった対象クーポンを適用
                        }
                    }
                }


                // --- 最安値適用 ---
                $prices = array_filter([$unit_price,$campaign_set_price,$coupon_set_price], fn($v)=>$v!==null);
                $applied_unit_price = !empty($prices) ? min($prices) : $unit_price;

                // dd($row->seller_id, $unit_price,$campaign_set_price,$coupon_set_price);

                // 適用ラベル
                if($applied_unit_price === $campaign_set_price) $applied_label='キャンペーン適用';
                elseif($applied_unit_price === $coupon_set_price) $applied_label='クーポン適用';
                else $applied_label='通常価格';

                if($taxCategory === '免税業者'){
                    $total_with_tax = ($applied_unit_price + $unit_price*($quantity-1)) + $shipFee*$quantity;
                    $data = [
                        optional($row->purchase_date)->format('Y-m-d'),
                        optional($row->confirmed_at)->format('Y-m-d'),
                        optional($row->pay_transfer)->format('Y-m-d'),
                        $row->order_number,
                        $row->seller_name,
                        $row->seller_registration_number,
                        $row->product_name,
                        $quantity,
                        $unit_price,
                        $applied_unit_price,
                        $applied_label,
                        $quantity>1 ? $unit_price*($quantity-1) : 0,
                        $shipFee,
                        $total_with_tax,
                        $row->tax_category
                    ];
                } else {
                    $tax_first = floor($applied_unit_price * $tax_rate);
                    $taxable_others = $unit_price * ($quantity-1);
                    $tax_others = floor($taxable_others * $tax_rate);
                    $shipping_tax = floor($shipFee * $quantity * $tax_rate);
                    $total_with_tax = $applied_unit_price + $tax_first + $taxable_others + $tax_others + $shipFee*$quantity + $shipping_tax;

                    $data = [
                        optional($row->purchase_date)->format('Y-m-d'),
                        optional($row->confirmed_at)->format('Y-m-d'),
                        optional($row->pay_transfer)->format('Y-m-d'),
                        $row->order_number,
                        $row->seller_name,
                        $row->seller_registration_number,
                        $row->product_name,
                        $quantity,
                        $unit_price,
                        $tax_rate*100,
                        $applied_unit_price,
                        $tax_first,
                        $applied_unit_price + $tax_first,
                        $applied_label,
                        $taxable_others,
                        $tax_others,
                        $taxable_others + $tax_others,
                        $shipFee,
                        $shipping_tax,
                        $shipFee*$quantity + $shipping_tax,
                        $total_with_tax,
                        $tax_first + $tax_others + $shipping_tax,
                        $row->tax_category
                    ];
                }

                mb_convert_variables('SJIS-win','UTF-8',$data);
                fputcsv($file,$data);
            }

            fclose($file);

            // --- HTMLガイド（ダミーデータ） ---
            $htmlContent = view('sellers.sales.csv_guide', [
                'headers' => $headers,
                'dummyData' => true, // ダミーデータ用フラグ
            ])->render();
            $htmlPath = storage_path("app/tmp/{$filenameHtml}");
            file_put_contents($htmlPath, $htmlContent);

            // --- ZIPにまとめる ---
            $zipPath = storage_path("app/tmp/{$zipFilename}");
            $zip = new ZipArchive;
            if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE){
                $zip->addFile($csvPath,$filenameCsv);
                $zip->addFile($htmlPath,$filenameHtml);
                $zip->close();
            }

            // ダウンロード
            return response()->download($zipPath)->deleteFileAfterSend(true);
        }

        return view('sellers.sales.sales_index', compact('subOrders', 'sales' , 'headers', 'finalOrders'));
    }

    public function slip2()
    {
        $headers = [
            '購入日', '取引日(到着確認日)', '入金日(手数料以外)', '注文番号', '出品者名称', '出品者登録番号',
            '商品名', '数量', '単価(税抜)', '税率(%)', '消費税額(商品)', '合計金額',
            '配送料(税抜)', '消費税額(配送料)', '税込金額(配送料)', 
            '税込金額合計', '消費税合計', '税区分'
        ];

        $subOrders = SubOrder::where('seller_id', auth()->id())
            ->with(['invoiceUser', 'invoiceSubOrder_item', 'invoiceArrivalReport', 'arrivalReport'])
            ->orderByDesc('id')
            ->get();

        $sales = $subOrders->map(function ($order) {
            $purchase_date = Carbon::parse($order->created_at);
            $confirmedDate = $order->arrivalReport ? Carbon::parse($order->arrivalReport->confirmed_at) : null;
            $payoutDate = $order->transferred_at ? Carbon::parse($order->transferred_at) : null;
            $shop = $order->invoiceUser->shop ?? null;
            $tax_rate = TaxRate::current()?->rate ?? 0;

            $items = $order->invoiceSubOrder_item->map(function ($item) use ($order, $shop, $tax_rate) {
                $product = Product::find($item->product_id);
                $shippingFee = $product->shipping_fee ?? 0;

                // --- キャンペーン ---
                $campaign = Campaign::where('shop_id', $shop->id ?? 0)
                    ->where('start_date', '<=', $order->created_at)
                    ->where('end_date', '>=', $order->created_at)
                    ->orderByDesc('dicount_rate1')
                    ->first();

                // dd($campaign);
                $campaign_price = null;
                if($campaign && $item->quantity >= 2){
                    $campaign_price = floor($item->price * (1 - $campaign->dicount_rate1));
                }

                // --- クーポン ---
                $coupon_price = null;
                if(!empty($item->coupon_id)){
                    $couponCodes = explode(',', $item->coupon_id);
                    $coupons = ShopCoupon::whereIn('id', $couponCodes)->get();
                    $coupon_candidates = [];
                    foreach($coupons as $coupon){
                        if($coupon->product_id == $item->product_id && $item->quantity >= 2){
                            $coupon_candidates[] = max(0, $item->price + $coupon->value);
                        }
                    }
                    if(!empty($coupon_candidates)){
                        $coupon_price = min($coupon_candidates);
                    }
                }
                // dd($item);

                // --- 最安値1個目 ---
                $prices = array_filter([$item->price, $campaign_price, $coupon_price], fn($v) => $v !== null);
                $lowest_price = !empty($prices) ? min($prices) : $item->price;
                // dd($shop);
                // dd($item->price, $campaign_price, $coupon_price);
                // 適用ラベル
                $applied_label = '通常価格';
                if($lowest_price === $campaign_price) $applied_label = 'キャンペーン適用';
                if($lowest_price === $coupon_price) $applied_label = 'クーポン適用';

                return [
                    'product_name' => $product->name ?? '不明な商品',
                    'quantity' => $item->quantity,
                    'unit_price' => $item->price,
                    'lowest_price' => $lowest_price,
                    'applied_label' => $applied_label,
                    'remaining_price' => $item->quantity > 1 ? $item->price : 0,
                    'shipping_fee' => $shippingFee,
                    'tax_category' => $shop->invoice_number ?? '非課税',
                ];
            })->toArray();

            return (object)[
                'purchase_date' => $purchase_date,
                'confirmed_at' => $confirmedDate,
                'payout_date' => $payoutDate,
                'order_id' => $order->id,
                'shop_name' => $shop->name ?? '',
                'invoice_number' => $shop->invoice_number ?? '0',
                'items' => $items,
            ];
        });

        return view('sellers.sales.sales_slip2', compact('sales', 'headers'));
    }




}
