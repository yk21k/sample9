<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreOrderRequest;
use App\Http\Requests\UpdateOrderRequest;

use App\Models\Order;
use App\Models\Product;
use App\Models\OrderItem;
use App\Models\SubOrder;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Stripe\Stripe;
use Stripe\Customer;
use Stripe\PaymentIntent;
use Stripe\Exception\ApiErrorException;
// use App\Models\Order;  // Order モデルをインポート
use App\Exports\FullOrderExport;
use Maatwebsite\Excel\Facades\Excel;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {

    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }


    public function store(StoreOrderRequest $request, Product $product)
    {   
        // 👇ここに追記
        \Log::info('注文受信', [
            'request' => $request->all()
        ]);
        \Log::info('受信データ', $request->all()); // ← 確認用

        Log::info('store メソッドが呼ばれました');
        // Stripe APIのシークレットキーを設定
        Stripe::setApiKey(env('STRIPE_SECRET'));

        // フォームから送信された payment_method を取得
        $data = $request->json()->all();
        Log::info('受信したデータ', $data);


        $paymentMethod = $data['payment_method'] ?? null;

        // JSONのみで来た場合に備えて強制マージ
        if ($paymentMethod) {
            $request->merge(['payment_method' => $paymentMethod]);
        }


        // カートの合計金額をセッションから取得
        $cartTotal = session('total_and_shipping');

        if (!$cartTotal) {
            return response()->json(['status' => 'error', 'message' => 'カートにアイテムがありません。'], 400);
        }

        try {

            // 支払いが成功した場合
            
                Log::info('決済成功。注文作成処理開始');

                // 支払いが成功したので、オーダーを作成
                $request->validate([
                    'shipping_fullname' => 'required',
                    'shipping_state' => 'required',
                    'shipping_city' => 'required',
                    'shipping_address' => 'required',
                    'shipping_phone' => 'required',
                    'shipping_zipcode' => 'required',
                    'payment_method' => 'required',
                ]);
                $paymentMethod = $data['payment_method'] ?? null;

                Log::info('バリデーション成功、オーダー作成処理開始');
                $order = new Order();
                $order->order_number = uniqid('OrderNumber-');
                $order->shipping_fullname = $request->input('shipping_fullname');
                $order->shipping_state = $request->input('shipping_state');
                $order->shipping_city = $request->input('shipping_city');
                $order->shipping_address = $request->input('shipping_address');
                $order->shipping_phone = $request->input('shipping_phone');
                $order->shipping_zipcode = $request->input('shipping_zipcode');

                // 請求先情報を処理（送付先情報がない場合、送付先情報を請求先として使用）
                if (!$request->has('billing_fullname')) {
                    $order->billing_fullname = $request->input('shipping_fullname');
                    $order->billing_state = $request->input('shipping_state');
                    $order->billing_city = $request->input('shipping_city');
                    $order->billing_address = $request->input('shipping_address');
                    $order->billing_phone = $request->input('shipping_phone');
                    $order->billing_zipcode = $request->input('shipping_zipcode');
                } else {
                    $order->billing_fullname = $request->input('billing_fullname');
                    $order->billing_state = $request->input('billing_state');
                    $order->billing_city = $request->input('billing_city');
                    $order->billing_address = $request->input('billing_address');
                    $order->billing_phone = $request->input('billing_phone');
                    $order->billing_zipcode = $request->input('billing_zipcode');
                }

                // クーポンコードがある場合、オーダーに設定
                // $order->coupon_code = Session::get('coupon101');
                
                $appliedCoupons = Session::get('applied_coupon_codes', []);

                if (!is_array($appliedCoupons)) {
                    Log::warning('applied_coupon_codes の値が配列ではありません', [
                        'value' => $appliedCoupons,
                        'type' => gettype($appliedCoupons)
                    ]);
                    $appliedCoupons = [];
                }
                
                Log::debug('適用されたクーポンコード一覧:', [
                    'applied_coupon_codes' => $appliedCoupons
                ]);
                $order->coupon_code = implode(',', $appliedCoupons);

                $order->grand_total = $cartTotal;
                $order->item_count = \Cart::session(auth()->id())->getTotalQuantity();
                $order->user_id = auth()->id();

                // 支払い方法を設定
                $order->payment_method = $request->input('payment_method');
                
                // dd($order->item_count);
                // オーダーを保存
                $order->save();

                // カートアイテムを保存
                $cartItems = \Cart::session(auth()->id())->getContent();
                
                foreach ($cartItems as $item) {
                    $product = Product::find($item->id);
                    if (is_null($product->shop_id)) {
                        Log::warning("shop_id が null の商品があります: 商品ID={$product->id}");
                    }

                    // デフォルトの価格（カート上の価格）
                    $base_price = $item->price;



                    // 注文アイテムを保存（最終価格で）
                    $order->items()->attach($item->id, [
                        'price' => $item->price,
                        'quantity' => $item->quantity,
                    ]);

                    // 在庫更新
                    $product = Product::find($item->id);
                    $product->decrement('stock', $item->quantity);
                }
                    
                // その他のオーダー関連処理
                $order->generateSubOrders();
                Log::info('generateSubOrders 呼び出し確認');
                $order->generateFavoritesSalesRate();
                $order->generateFavoritesDisplay();

                // カートを空にする
                \Cart::session(auth()->id())->clear();

                // 支払い成功のレスポンス
                return response()->json([
                    'status' => 'success',
                    'message' => '決済が成功しました！',
                    'order_id' => $order->id,  // 注文IDなど
                ], 200);  // 200は成功のHTTPステータスコード
            
        }catch (ApiErrorException $e) {
        // Stripeのエラーハンドリング
        Log::error('Stripeエラー: ' . $e->getMessage());
        return response()->json([
            'status' => 'error',
            'message' => '決済処理中にエラーが発生しました: ' . $e->getMessage(),
        ], 500);
        } catch (\Exception $e) {
            // その他のエラー
            Log::error('一般エラー: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => '決済処理中にエラーが発生しました: ' . $e->getMessage(),
            ], 500);
        }
    }


    /**
     * Display the specified resource.
     */
    public function show(Order $order)
    {

    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Order $order)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateOrderRequest $request, Order $order)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Order $order)
    {
        //
    }

    public function exportFullOrders()
    {
        return Excel::download(new FullOrderExport, 'all_orders_with_items.xlsx');
    }


}
