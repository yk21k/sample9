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

    /**
     * Store a newly created resource in storage.
     */
    // public function store(StoreOrderRequest $request, Product $product)
    // {
        
    //     // Stripe APIのシークレットキーを設定
    //     Stripe::setApiKey(env('STRIPE_SECRET'));

    //     // フォームから送信された payment_method を取得
    //     $paymentMethod = $request->input('payment_method');

    //     // カートの合計金額をセッションから取得
    //     $cartTotal = session('cart_total'); // セッションに格納されたカート合計金額を取得
    //     if (!$cartTotal) {
    //         return back()->with('error', 'カートにアイテムがありません。');
    //     }

    //     try {
    //         // 顧客情報を作成
    //         $customer = Customer::create([
    //             'email' => auth()->user()->email,  // 顧客のメールアドレス（ログイン者から取得）
    //             'payment_method' => $paymentMethod,  // クライアントから送信された payment_method
    //             'invoice_settings' => [
    //                 'default_payment_method' => $paymentMethod, // 顧客にデフォルトの支払い方法を設定
    //             ],
    //         ]);

    //         // PaymentIntentを作成して支払いを処理
    //         $paymentIntent = PaymentIntent::create([
    //             'amount' => $cartTotal,  // Stripeは最小通貨単位で金額を受け取るので、100倍します（例: 1000円なら1000）
    //             'currency' => 'jpy',           // 通貨（日本円）
    //             'customer' => $customer->id,   // 顧客ID
    //             'payment_method' => $paymentMethod,
    //             'confirmation_method' => 'manual', // 手動確認
    //             'confirm' => true,              // 即時確認
    //             'return_url' => route('payment.success') // 支払い後のリダイレクト先URL
    //         ]);

    //         // 支払いが成功した場合
    //         if ($paymentIntent->status === 'succeeded') {
    //             // 支払いが成功したので、オーダーを作成

    //     // dd($request->all());ーーーー

    //     $request->validate([
    //         'shipping_fullname' => 'required',
    //         'shipping_state' => 'required',
    //         'shipping_city' => 'required',
    //         'shipping_address' => 'required',
    //         'shipping_phone' => 'required',
    //         'shipping_zipcode' => 'required',
    //         'payment_method' => 'required',
    //     ]);

    //     $order = new Order();

    //     $order->order_number = uniqid('OrderNumber-');

    //     $order->shipping_fullname = $request->input('shipping_fullname');
    //     $order->shipping_state = $request->input('shipping_state');
    //     $order->shipping_city = $request->input('shipping_city');
    //     $order->shipping_address = $request->input('shipping_address');
    //     $order->shipping_phone = $request->input('shipping_phone');
    //     $order->shipping_zipcode = $request->input('shipping_zipcode');

    //     if(!$request->has('billing_fullname')) {
    //         $order->billing_fullname = $request->input('shipping_fullname');
    //         $order->billing_state = $request->input('shipping_state');
    //         $order->billing_city = $request->input('shipping_city');
    //         $order->billing_address = $request->input('shipping_address');
    //         $order->billing_phone = $request->input('shipping_phone');
    //         $order->billing_zipcode = $request->input('shipping_zipcode');
    //     }else {
    //         $order->billing_fullname = $request->input('billing_fullname');
    //         $order->billing_state = $request->input('billing_state');
    //         $order->billing_city = $request->input('billing_city');
    //         $order->billing_address = $request->input('billing_address');
    //         $order->billing_phone = $request->input('billing_phone');
    //         $order->billing_zipcode = $request->input('billing_zipcode');
    //     }
    //     // dd(Session::get('coupon101'));

    //     $order->coupon_code = Session::get('coupon101');

    //     $order->grand_total = \Cart::session(auth()->id())->getTotal();
    //     $order->item_count = \Cart::session(auth()->id())->getContent()->count();

    //     $order->user_id = auth()->id();
    //     // $order->user_id = $request->user()->id;

    //     if (request('payment_method') == 'paypal') {
    //         $order->payment_method = 'paypal';
    //     }
    //     elseif (request('payment_method') == 'stripe') {
    //         $order->payment_method = 'stripe';
    //     }

    //     $order->save();

    //     // save order items

    //     $cartItems = \Cart::session(auth()->id())->getContent();

    //     // dd($cartItems);

    //     foreach ($cartItems as $item) {
    //         $order->items()->attach($item->id, ['price' => $item->price, 'quantity' => $item->quantity]);

    //         $update_stock = new Product;
    //         $update_stock->where('id', '=', $item->id)->decrement('stock', $item->quantity);
    //         // $order_goods = \Cart::session(auth()->id())->getContent($item->id);
    //         // $order_goods = $item->quantity;
    //         // $order_goods = $order->items()->$item->quantity;
    //         // dd($order->items()->quantity);
    //         // dd($cartItems);
    //         // dd($product_stocks->stock);
    //         // dd($item->quantity);

    //     }
    //     // dd($order->items());
    //     // dd($request->qty);
        

    //     // $product_stocks = Product::find($item->id);
    //         // Product::where('id', '=', $item->id)->update(['stock' => $product_stocks->stock - $first_stock->quantity]);
    //     // Product::where('id', '=', $product->id)->update(['stock' => $product_stocks->stock - $first_stock->quantity]);

    //     // dd($cartItems->id);

    //     // $first_stocks = \Cart::session(auth()->id())->getContent($product->id);
    //     // dd($first_stocks);

    //     // foreach($first_stocks as $first_stock)
    //     // {
    //     //     $first_stock->quantity;
    //     //     $product_stocks = Product::find($first_stock->id);

    //     // }
    //     // dd($first_stock->quantity, $product_stocks->stock);

        
    //     // $test =[];
    //     // $test = $order->items();
    //     // dd($test);
    //     // dd($order_goods);
    //     // dd($order_goods->id);
    //     // dd($product_stocks);

    //     // Product::where('id', '=', $product->id)->update(['stock' => $product_stocks->stock - $first_stock->quantity]);

    //     // $product_stocks = Product::find($item->id);
    //     // $cart_quantity = OrderItem::where('order_id', '=', $order->id)->get();
    //     // dd($product_stocks->stock, $cart_quantity->product());

    //     $order->generateSubOrders();
    //     $order->generateFavoritesSalesRate();
    //     $order->generateFavoritesDisplay();

    //      // empty cart

    //     \Cart::session(auth()->id())->clear();
        
    //     // payment
    //     if (request('payment_method') == 'paypal')
    //     {
    //         // redirect pp
    //         return redirect()->route('paypal.checkout', $order->id);
    //     }
    //     elseif(request('payment_method')  == 'stripe')
    //     {
    //         return redirect()->route('/home');  // payment.blade.phpに遷移

    //     }

       

    //     // send email to customer

    //     // take user to thank you

    //     return redirect()->route('home')->withMessage('Order has been placed'); 
    //     // ---------------------------------------

    //             // オーダー作成後に成功メッセージを返す
    //             return redirect()->route('orders.success');
    //         } else {
    //             return back()->with('error', '決済に失敗しました。');
    //         }
    //     } catch (ApiErrorException $e) {
    //         // エラーハンドリング
    //         return back()->with('error', '決済処理中にエラーが発生しました: ' . $e->getMessage());
    //     }





    // }

    public function store(StoreOrderRequest $request, Product $product)
    {   
        // dd(Session::get('coupon101'));
        // Stripe APIのシークレットキーを設定
        Stripe::setApiKey(env('STRIPE_SECRET'));

        // フォームから送信された payment_method を取得
        $paymentMethod = $request->input('payment_method');

        // カートの合計金額をセッションから取得
        $cartTotal = session('cart_total');

        if (!$cartTotal) {
            return response()->json(['status' => 'error', 'message' => 'カートにアイテムがありません。'], 400);
        }

        try {
            // 顧客情報を作成
            $customer = Customer::create([
                'email' => auth()->user()->email,  // 顧客のメールアドレス（ログイン者から取得）
                'payment_method' => $paymentMethod,  // クライアントから送信された payment_method
                'invoice_settings' => [
                    'default_payment_method' => $paymentMethod, // 顧客にデフォルトの支払い方法を設定
                ],
            ]);

            // PaymentIntentを作成して支払いを処理
            $paymentIntent = PaymentIntent::create([
                'amount' => $cartTotal,  // Stripeは最小通貨単位で金額を受け取るので、100倍します（例: 1000円なら1000）
                'currency' => 'jpy',           // 通貨（日本円）
                'customer' => $customer->id,   // 顧客ID
                'payment_method' => $paymentMethod,
                'confirmation_method' => 'manual', // 手動確認
                'confirm' => true,              // 即時確認
                'return_url' => route('payment.success') // 支払い後のリダイレクト先URL
            ]);

            // 支払いが成功した場合
            if ($paymentIntent->status === 'succeeded') {
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



                $order->grand_total = \Cart::session(auth()->id())->getTotal();
                $order->item_count = \Cart::session(auth()->id())->getTotalQuantity();
                $order->user_id = auth()->id();

                // 支払い方法を設定
                $order->payment_method = $request->input('pay');
                
                // dd($order->item_count);
                // オーダーを保存
                $order->save();

                // カートアイテムを保存
                $cartItems = \Cart::session(auth()->id())->getContent();
                // foreach ($cartItems as $item) {
                //     $product = Product::find($item->id);
                //     if (is_null($product->shop_id)) {
                //         Log::warning("shop_id が null の商品があります: 商品ID={$product->id}");
                //     }
                    
                //     // キャンペーン割引価格が設定されていればそれを使う
                //     $priceToUse = $item->discounted_price ?? $item->price;


                //         // クーポン割引
                //     $couponDiscount = 0;
                //     foreach ((array) $item->getConditions() as $condition) {
                //         $value = $condition->getValue();
                //         $couponDiscount += is_string($value) ? floatval($value) : 0;
                //     }

                //     $final_price = $item->price + $couponDiscount;

                //     // 表示上の最終価格（割引がある方を選択）
                //     $lowest_price = min($discounted_price, $final_price);

                //     // 注文アイテムを関連付け
                //     $order->items()->attach($item->id, [
                //         'price' => $priceToUse,
                //         'quantity' => $item->quantity
                //     ]);

                //     // 在庫を更新
                //     $product = Product::find($item->id);
                //     $product->decrement('stock', $item->quantity);
                // }

                foreach ($cartItems as $item) {
                    $product = Product::find($item->id);
                    if (is_null($product->shop_id)) {
                        Log::warning("shop_id が null の商品があります: 商品ID={$product->id}");
                    }

                    // デフォルトの価格（カート上の価格）
                    $base_price = $item->price;

                    // キャンペーン割引（存在すれば）
                    $discounted_price = $base_price;

                    $shopId = $product->shop_id ?? null;
                    $campaign = \App\Models\Campaign::where('status', 1)
                        ->where('shop_id', $shopId)
                        ->where('start_date', '<=', now())
                        ->where('end_date', '>=', now())
                        ->orderByDesc('dicount_rate1')
                        ->first();

                    if ($campaign) {
                        $discounted_price = ceil($base_price * (1 - $campaign->dicount_rate1));
                    }

                    // クーポン割引
                    $couponDiscount = 0;
                    foreach ((array) $item->getConditions() as $condition) {
                        $value = $condition->getValue();
                        $couponDiscount += is_string($value) ? floatval($value) : 0;
                    }

                    $final_price = $base_price + $couponDiscount;

                    // 表示上の最終価格（割引がある方を選択）
                    $lowest_price = min($discounted_price, $final_price);

                    // 注文アイテムを保存（最終価格で）
                    $order->items()->attach($item->id, [
                        'price' => $lowest_price,
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
            } else {
            // 支払い失敗時
            return response()->json([
                'status' => 'error',
                'message' => '決済に失敗しました。',
            ], 400);
            }
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
}
