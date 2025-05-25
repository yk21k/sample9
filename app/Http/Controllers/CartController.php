<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Coupon;
use App\Models\ShopCoupon;
use App\Models\Order;
use App\Models\SubOrder;
use App\Models\Campaign;

use App\Models\DeliveryAddress;
use App\Models\Auction;
use Illuminate\Support\Facades\View;

use Illuminate\Support\Facades\DB;
use Session;
use Auth;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;


class CartController extends Controller
{
    // public function add($productId)
    // {
    //     dd($productId);
    //     $product = Product::find($productId);
    // }

    public function add(Product $product)
    {
        // dd($product);

        // add the product to cart
        \Cart::session(auth()->id())->add(array(
            'id' => (string)$product->id,
            'name' => $product->name,
            'price' => $product->price,
            'quantity' => 1,
            'attributes' => array(),
            // 'attributes' => [
            //     'product_id' => $product->id, // 👈 必須！
            // ],
            'associatedModel' => $product

        ));

        DB::beginTransaction();

        $first_stocks = \Cart::session(auth()->id())->getContent($product->id);
        // dd($first_stocks);

        foreach($first_stocks as $first_stock)
        {
            $first_stock->quantity;
                
            // item has attribute quantity
            $product_stocks = Product::find($product->id);

            // dd($product_stocks);
            // dd($product_stocks->stock, $first_stock->quantity);
            // dd($first_stock->quantity);

            if($first_stock->quantity > $product_stocks->stock){
                // dd('stock');
                \Cart::session(auth()->id())->remove($product->id);
                DB::rollBack();

                return redirect()->route('cart.index')->with('message', 'Not enough stock for your order');
            }

        }
        // $product_stocks->update(['stock' => $product_stocks->stock - $first_stock->quantity]);
        // Product::where('id', '=', $product->id)->update(['stock' => $product_stocks->stock - $first_stock->quantity]);
        DB::commit();
        // dd($first_stock->quantity);


        return redirect()->route('cart.index');
    }

    public function addAuction(Auction $auction)
    {
        // add the auction to cart
        \Cart::session(auth()->id())->add(array(
            'id' => $auction->id,
            'name' => $auction->name,
            'price' => $auction->spot_price,
            'quantity' => 1,
            'attributes' => array(),
            'associatedModel' => $auction

        ));
        return redirect()->route('cart.index');
    }

    public function index()
    {   
        $cartItems = \Cart::session(auth()->id())->getContent();

        $today = Carbon::today();

        // 有効なキャンペーン取得
        $campaigns = Campaign::where('status', 1)
            ->where('start_date', '<=', $today)
            ->where('end_date', '>=', $today)
            ->get();

        // ✅ 1. カートの商品を提供価格 or 通常価格で再構築
        foreach ($cartItems as $item) {
            $product = Product::find($item->id);

            // if ($product) {
            //     \Cart::remove($item->id);
            //     // $priceToUse = $product->offer_price ?? $product->price;
            //     $priceToUse = $product->price;

            //     \Cart::add([
            //         'id' => $product->id,
            //         'name' => $product->name,
            //         'price' => $priceToUse,
            //         'quantity' => $item->quantity,
            //         'attributes' => [],
            //         'associatedModel' => $product, // ✅ ここで設定
            //     ]);
            // }
            if ($product) {
                $priceToUse = $product->offer_price ?? $product->price;

                // price のみを更新。CartCondition は維持
                \Cart::session(auth()->id())->update($item->id, [
                    'price' => $priceToUse
                ]);

                // associatedModel の更新（必要なら session 書き換え）
                $item->associatedModel = $product;
            }            
        }

        // ✅ 2. 再構築されたカートを取得
        // $items = \Cart::getContent();
        $items = \Cart::session(auth()->id())->getContent();


        // ✅ 3. 割引処理（この時点で associatedModel は null ではない）
        $discountedCarts = $items->map(function ($item) use ($campaigns) {
            $shopId = $item->associatedModel?->getOriginal('shop_id');

            // --- キャンペーン価格の算出 ---
            $matchingCampaign = $campaigns->where('shop_id', $shopId)->sortByDesc('dicount_rate1')->first();

            if ($matchingCampaign) {
                $discountRate = $matchingCampaign->dicount_rate1;
                $item->discounted_price = ceil($item->price - ceil($item->price*$discountRate));
                $item->campaign = $matchingCampaign;
            } else {
                $item->discounted_price = $item->price;
                $item->campaign = null;
            }

            // // --- クーポン割引の反映（CartCondition） ---
            // $couponDiscount = 0;
            // foreach ((array) $item->getConditions() as $condition) {
            //     $couponDiscount += floatval($condition->getValue());
            // }
            // // dd($couponDiscount);
            // $item->final_price = $item->price + $couponDiscount; // 例: price=1000, value=-100 ⇒ 900


            $couponDiscount = 0;
            foreach ((array) $item->getConditions() as $condition) {
                $value = $condition->getValue();
                $couponDiscount += is_string($value) ? floatval($value) : 0;
                Log::debug("クーポン条件", [
                '値' => $condition->getValue(),
                '文字列か？' => is_string($condition->getValue()),
                ]);
            }

            $item->final_price = $item->price + $couponDiscount;            

            // --- 最終的な表示価格を選ぶ ---
            $item->lowest_price = min([
                $item->discounted_price,
                $item->final_price,
            ]); 

            // ✅ デバッグログをここに書く
            Log::debug("カート商品の割引データ確認", [
                '商品ID' => $item->id,
                '商品名' => $item->name,
                '通常価格' => $item->price,
                'クーポン割引' => $couponDiscount,
                'キャンペーン割引後価格' => $item->discounted_price,
                'クーポン適用後価格' => $item->final_price,
                '最終表示価格（lowest_price）' => $item->lowest_price,
            ]);

            Log::debug("条件一覧", [
                'conditions' => $item->getConditions()
            ]);

            return $item;
        });



        // ✅ 4. 合計を再計算してセッションに保存
        $total = 0;
        // foreach ($discountedCarts as $item) {
        //     $total += $item->discounted_price * $item->quantity;
        // }
        // dd($discountedCarts,$item->discounted_price, $item->final_price, $item->quantity);
        $total = $discountedCarts->reduce(function ($carry, $item) {
            return $carry + ($item->lowest_price * $item->quantity);
        }, 0);

        session(['cart_total' => $total]);
        // dd(ceil($item->price - ceil($item->price*0.05)), number_format($item->price - ($item->price*0.05)));

        // ✅ 5. ビューに渡す
        // $cartItems = \Cart::getContent();
        // dd($cartItems);

        $cartItems = collect(session('cart_items'))->map(function ($item) {
            return (object) $item;
        });

        session(['cart_items' => $cartItems]);


        // foreach ($cartItems as $item) {
        //     echo "商品名: {$item->name}, 数量: {$item->quantity}, 小計: {$item->discounted_price}{$item->price}円<br>";
        // }
        $cartItems = $discountedCarts;
        

       return view('cart.index', compact('cartItems', 'items', 'discountedCarts', 'total'));
    }





    //     public function index()
    // {   
    //     $cartItems = \Cart::session(auth()->id())->getContent();

    //     $today = Carbon::today();

    //     // 有効なキャンペーン取得
    //     $campaigns = Campaign::where('status', 1)
    //         ->where('start_date', '<=', $today)
    //         ->where('end_date', '>=', $today)
    //         ->get();

    //     // ✅ 1. カートの商品を提供価格 or 通常価格で再構築
    //     foreach ($cartItems as $item) {
    //         $product = Product::find($item->id);

    //         if ($product) {
    //             \Cart::remove($item->id);

    //             $priceToUse = $product->offer_price ?? $product->price;

    //             \Cart::add([
    //                 'id' => $product->id,
    //                 'name' => $product->name,
    //                 'price' => $priceToUse,
    //                 'quantity' => $item->quantity,
    //                 'attributes' => [],
    //                 'associatedModel' => $product, // ✅ ここで設定
    //             ]);
    //         }
    //     }

    //     // ✅ 2. 再構築されたカートを取得
    //     $items = \Cart::getContent();

    //     // ✅ 3. 割引処理（この時点で associatedModel は null ではない）
    //     $discountedCarts = $items->map(function ($item) use ($campaigns) {
    //         $shopId = $item->associatedModel?->getOriginal('shop_id');

    //         // --- キャンペーン価格の算出 ---
    //         $matchingCampaign = $campaigns->where('shop_id', $shopId)->sortByDesc('dicount_rate1')->first();

    //         if ($matchingCampaign) {
    //             $discountRate = $matchingCampaign->dicount_rate1;
    //             $item->discounted_price = ceil($item->price * (1 - $discountRate));
    //             $item->campaign = $matchingCampaign;
    //         } else {
    //             $item->discounted_price = $item->price;
    //             $item->campaign = null;
    //         }

    //         // --- クーポン割引の反映（CartCondition） ---
    //         $couponDiscount = 0;
    //         foreach ((array) $item->getConditions() as $condition) {
    //             $couponDiscount += floatval($condition->getValue());
    //         }

    //         $item->final_price = $item->price + $couponDiscount; // 例: price=1000, value=-100 ⇒ 900

    //         // --- 最終的な表示価格を選ぶ ---
    //         $item->lowest_price = min([
    //             $item->discounted_price,
    //             $item->final_price,
    //         ]);    

    //         return $item;
    //     });

    //     // ✅ 4. 合計を再計算してセッションに保存
    //     $total = 0;
    //     // foreach ($discountedCarts as $item) {
    //     //     $total += $item->discounted_price * $item->quantity;
    //     // }

    //     $total = $discountedCarts->reduce(function ($carry, $item) {
    //         return $carry + ($item->lowest_price * $item->quantity);
    //     }, 0);
        
    //     session(['cart_total' => $total]);


    //     // ✅ 5. ビューに渡す
    //     $cartItems = \Cart::getContent();
    //     // dd($cartItems);

    //     // $cartItems = collect(session('cart_items'))->map(function ($item) {
    //     //     return (object) $item;
    //     // });

    //     session(['cart_items' => $cartItems]);


    //     // foreach ($cartItems as $item) {
    //     //     echo "商品名: {$item->name}, 数量: {$item->quantity}, 小計: {$item->discounted_price}{$item->price}円<br>";
    //     // }

    //    return view('cart.index', compact('cartItems', 'items', 'discountedCarts', 'total'));
    // }



    public function destroy(Product $product, $itemId)
    {   
        // dd($itemId);

        // $first_stocks = \Cart::session(auth()->id())->getContent($itemId);
        // // dd($first_stocks);

        // foreach($first_stocks as $first_stock)
        // {
        //     $first_stock->quantity;
        //     // dd($first_stock->id);
        //     $product_stocks = Product::find($itemId);
        //     // dd($product_stocks->stock, $first_stock->quantity);

        // }
        // Product::where('id', '=', $itemId)->update(['stock' => $product_stocks->stock + $first_stock->quantity]);
        \Cart::session(auth()->id())->remove($itemId);

        
        return back();
    }

    public function update(Product $product, $rowId)
    {
        // 5 + 2 which results to 7 if updated relatively..
        \Cart::session(auth()->id())->update($rowId,[
            'quantity' => array(
              'relative' => false,
              'value' => request('quantity')
          ),
        ]);

        DB::beginTransaction();

        $first_stocks = \Cart::session(auth()->id())->getContent($product->id);
        // dd($first_stocks);

        foreach($first_stocks as $first_stock)
        {
            $first_stock->quantity;
            // dd($first_stock->quantity);
            // dd($first_stock->id);

                
            // item has attribute quantity
            $product_stocks = Product::find($first_stock->id);

            // dd($product_stocks);
            // dd($product_stocks->stock, $first_stock->quantity);
            // dd($product_stocks, $first_stock);
            // dd($first_stock->quantity);

            if($first_stock->quantity > $product_stocks->stock){
                // dd('stock');
                \Cart::session(auth()->id())->remove($product->id);
                DB::rollBack();

                return redirect()->route('cart.index')->with('message', 'Not enough stock for your order');
            }    


        }
        // $product_stocks->update(['stock' => $product_stocks->stock - $first_stock->quantity]);
        // Product::where('id', '=', $first_stock->id)->update(['stock' => $product_stocks->stock - $first_stock->quantity]);
        DB::commit();
        // dd($first_stock->quantity);


        return back();

    }

    public function checkout(Request $request, Product $product)
    {
        // dd(\Cart::session(auth()->id())->getContent());
        // dd(\Cart::session(auth()->id())->getTotalQuantity());
        // dd(Session::get('coupon101'));


        $cartProducts = \Cart::session(auth()->id())->getContent();
        $cartPrices = \Cart::getTotal();
        // dd($cartPrices);
        // session(['cart_total' => $cartPrices]);ここでセッションに保存している 
        // dd($cartProducts); shop_coupon
        // dd($cartPrices);
        
        // dd($cartProducts); 

        $cartItems = \Cart::getContent();
        Log::debug('カートのアイテム:', ['cart_items' => $cartItems]);
        $allCouponCodes = [];

        foreach ($cartItems as $item) {
            $conditions = (array) $item->getConditions();
            Log::debug('アイテムの条件:', ['item_conditions' => $conditions]);
            foreach ($conditions as $condition) {
                $attributes = $condition->getAttributes();
                Log::debug('条件の属性:', ['attributes' => $attributes]);
                if (isset($attributes['code'])) {
                    $allCouponCodes[] = $attributes['code'];
                }
            }
        }
        Log::debug('クーポンコードリスト:', ['coupon_codes' => $allCouponCodes]);
        // 重複を削除してセッションに保存（文字列としても配列としてもOK）
        $allCouponCodes = array_unique($allCouponCodes);
        Log::debug('重複を削除後のクーポンコード:', ['coupon_codes' => $allCouponCodes]);
            // ログで確認

        // 例: カンマ区切り文字列で保存
        // Session::put('applied_coupon_codes', implode(',', $allCouponCodes));


        // または配列のまま保存したいなら　重要
        Session::put('applied_coupon_codes', $allCouponCodes);

        
            $searchStock = [];
            foreach ($cartProducts as $cartProduct) {
                // dd($cartProduct);
                // dd($cartProduct->associatedModel->shipping_fee);
                $searchStocks = $cartProduct->pluck('id')->toArray();
                // dd($searchStocks);
                foreach($searchStocks as $searchStock){
                    if($cartProduct->associatedModel->shipping_fee){

                    }else{
                        $stockProducts = Product::where('id', $searchStock)->get();
                        foreach($stockProducts as $stockProduct){
                            // dd($stockProduct->stock);

                            if($stockProduct->stock <= 0){
                                return back()->withMessage(" I'm sorry. You cannot purchase the item because the item in your cart was paid for first or there has been a change in inventory. Please empty your cart again and continue shopping. ");   
                            }    
                        }    
                    }   
                }        
            }    
        
        

        $deliveryAddresses = DeliveryAddress::where('user_id', Auth::user()->id)->get();
        // dd($deliveryAddresses);
        
        $setDeliPlaces = DeliveryAddress::setDeliPlaces();
        // dd($setDeliPlaces);
        // dd(!isset($setDeliPlaces));
        if(!isset($setDeliPlaces)){
            $setDeliPlaces;
        }

        $setCart = \Cart::session(auth()->id())->isEmpty();
        // dd($setCart);
        if($setCart){
            // dd('empty');
            return back()->withMessage('You cannot proceed to checkout. Please continue shopping');
        }else{
            return view('cart.checkout', compact('deliveryAddresses', 'setDeliPlaces', 'setCart'));

        }    
    }

    public function deliPlace(Request $request)
    {
        
        $data = $request->all();
        $deliPlaces = DeliveryAddress::where('user_id', Auth::user()->id)->get();
        foreach($deliPlaces as $deliPlace){
            if($deliPlace->status==1){
                $deliPlace->update([
                    'status' => 0
                ]);
            }
        }
        $upDeliPlaces = DeliveryAddress::find($data['shipping_id']);
        $upDeliPlaces->update(['status' => 1]);
        // dd($setDeliPlace->status);
        $setDeliPlaces = DeliveryAddress::where('user_id', Auth::user()->id)->get();
        return back();
    
    }

    public function applyCoupon()
    {
        $couponCode = request('coupon_code');

        $couponData = Coupon::where('code', $couponCode)->first();


        if(!$couponData) {
            return back()->withMessage('Sorry! Coupon does not exist');
        }
        //coupon logic
        $condition = new \Darryldecode\Cart\CartCondition(array(
            'name' => $couponData->name,
            'type' => $couponData->type,
            'target' => 'total',
            'value' => $couponData->value,
        ));

        \Cart::session(auth()->id())->condition($condition); // for a speicifc user's cart

        return back()->withMessage('coupon applied');

    }

    // public function applyShopCoupon()
    // {
    //     $shopCouponCode = request('code');
    //     // dd($shopCouponCode);

    //     $shopCouponData = ShopCoupon::where('code', $shopCouponCode)->first();
    //     // dd($shopCouponData->product_id);

    //     if(!$shopCouponData) {
    //         return back()->withMessage('申し訳ございません。クーポンは存在しません');
    //     }

    //     // $shopCouponOrder = Order::where('coupon_code', $shopCouponCode)->first();
    //     $shopCouponOrder = Order::where(function ($query) use ($shopCouponCode) {
    //         $query->where('coupon_code', $shopCouponCode)
    //               ->orWhere('coupon_code', 'LIKE', "$shopCouponCode,%")
    //               ->orWhere('coupon_code', 'LIKE', "%,$shopCouponCode")
    //               ->orWhere('coupon_code', 'LIKE', "%,$shopCouponCode,%");
    //     })->first();

    //     // dd($shopCouponOrder);

    //     if($shopCouponOrder) {
    //         return back()->withMessage('申し訳ございません。このクーポンはすでに支払い済みなのでご利用いただけません。');
    //     }    

    //     $cartItems = \Cart::session(auth()->id())->getContent();
    //     // foreach ($cartItems as $item) {
    //     //     dd($item->id); // ← ここが Cart に保存された ID
    //     // }
    //     $items = \Cart::getContent();
    //     // dd($cartItems, $items);
    //     // dd($shopCouponData->product_shop_coupon->name);
    //     // dd($shopCouponData->product_id);
    //     $pre_productID = $shopCouponData->product_id;

    //     $cartItems_toArray = $cartItems->toArray();

    //     // if (is_array($cartItems_toArray)) { 
    //     //     dd('array'); 
    //     // }
    //     // dd($cartItems_toArray);

    //     $filtered_items = array_filter($cartItems_toArray, function($item, $pre_productID) {
    //         return $item['id'] == $pre_productID;
    //     }, ARRAY_FILTER_USE_BOTH);

    //     // dd(!empty($filtered_items));



    //     // 条件を満たす要素が存在するか判定
    //     if (!empty($filtered_items)) {

    //             $productID = $shopCouponData->product_id;
    //             \Cart::clearItemConditions($productID);

    //             $condition = new \Darryldecode\Cart\CartCondition(array(
    //                 'name' => $shopCouponData->name,
    //                 'type' => $shopCouponData->type,
    //                 'target' => 'item',
    //                 'value' => $shopCouponData->value,
    //                 'attributes' => [
    //                     'code' => $shopCouponData->code,
    //                     'coupon_id' => $shopCouponData->id,
    //                     'product_id' => $shopCouponData->product_id,
    //                 ],
    //             ));

    //             \Cart::addItemCondition($productID, $condition);

    //             $appliedCoupons = Session::get('applied_coupon_codes', []);
    //             // 文字列だった場合は explode で配列に変換
    //             if (!is_array($appliedCoupons)) {
    //                 $appliedCoupons = explode(',', $appliedCoupons);
    //             }
    //             $appliedCoupons[] = $shopCouponData->code;
    //             $appliedCoupons = array_unique($appliedCoupons);

    //             Session::put('applied_coupon_codes', $appliedCoupons);

    //             // dd($condition, $appliedCoupons, $cartItems, $items);
    //             return back()->withMessage('クーポンを適用しました。.....もしも金額に変更がない場合は、その商品はクーポン対象外となります。店舗へご確認下さい。');

    //     } else {
    //             return back()->withMessage('注意！クーポンに該当する商品はありません。');

                
    //     }

    // }

    // public function applyShopCoupon()
    // {
    //     $shopCouponCode = request('code');
        
    //     // クーポンコードが存在するか確認
    //     $shopCouponData = ShopCoupon::where('code', $shopCouponCode)->first();

    //     if (!$shopCouponData) {
    //         return back()->withMessage('申し訳ございません。クーポンは存在しません');
    //     }

    //     // このクーポンがすでに使われたことがあるか確認（Orderに記録されているか）
    //     $shopCouponOrder = Order::where(function ($query) use ($shopCouponCode) {
    //         $query->where('coupon_code', $shopCouponCode)
    //               ->orWhere('coupon_code', 'LIKE', "$shopCouponCode,%")
    //               ->orWhere('coupon_code', 'LIKE', "%,$shopCouponCode")
    //               ->orWhere('coupon_code', 'LIKE', "%,$shopCouponCode,%");
    //     })->first();

    //     if ($shopCouponOrder) {
    //         return back()->withMessage('申し訳ございません。このクーポンはすでに使用済みです。');
    //     }

    //     // カート内の商品を取得
    //     $cartItems = \Cart::session(auth()->id())->getContent();
    //     $targetProductId = (string) $shopCouponData->product_id; 
    //     // dd($targetProductId);
    //     // カート内の該当商品にクーポン適用
    //     $matched = false;

    //     foreach ($cartItems as $item) {
    //         // associatedModel に product_id を保存している前提（なければ $item->id を直接比較）
    //         // $cartProductId = $item->associatedModel->id ?? null;
    //         // dd($item->id, $item->associatedModel->id ?? null, $shopCouponData->product_id);
    //         // $cartProductId = $item->associatedModel->id ?? $item->id;
    //         // 商品IDをstring型で比較
    //         $cartProductId = (string) ($item->associatedModel->id ?? null);

    //         if ($cartProductId == $targetProductId) {
    //             $matched = true;

    //             // 既存の条件を削除してからクーポンを適用
    //             \Cart::session(auth()->id())->clearItemConditions($item->id);

    //             $condition = new \Darryldecode\Cart\CartCondition([
    //                 'name' => $shopCouponData->name,
    //                 'type' => $shopCouponData->type, // e.g. 'discount'
    //                 'target' => 'item',
    //                 'value' => $shopCouponData->value, // e.g. '-10%' or '-100'
    //                 'attributes' => [
    //                     'code' => $shopCouponData->code,
    //                     'coupon_id' => $shopCouponData->id,
    //                     'product_id' => $shopCouponData->product_id,
    //                 ],
    //             ]);

    //             \Cart::session(auth()->id())->addItemCondition((string)$item->id, $condition);
    //             $cartItemAfter = \Cart::session(auth()->id())->get((string)$item->id);
    //             // dd($cartItemAfter->getConditions(), $item->id, $condition);

    //             // デバッグ出力
    //             dd(\Cart::session(auth()->id())->get($item->id));
    //         }
    //     }
        

    //     if (!$matched) {
    //         return back()->withMessage('注意！カート内にクーポン対象商品が見つかりませんでした。');
    //     }

    //     // セッションにクーポンコードを保存（重複しないように）
    //     $appliedCoupons = Session::get('applied_coupon_codes', []);
    //     if (!is_array($appliedCoupons)) {
    //         $appliedCoupons = explode(',', $appliedCoupons);
    //     }
    //     $appliedCoupons[] = $shopCouponData->code;
    //     $appliedCoupons = array_unique($appliedCoupons);

    //     Session::put('applied_coupon_codes', $appliedCoupons);
    //     // dd(\Cart::session(auth()->id())->getContent());

    //     return back()->withMessage('クーポンを適用しました。対象商品の金額が割引されているかご確認ください。');
    // }

    public function applyShopCoupon()
    {
        $shopCouponCode = request('code');
        // クーポンコードが存在するか確認
        $shopCouponData = ShopCoupon::where('code', $shopCouponCode)->first();

        if (!$shopCouponData) {
            return back()->withMessage('申し訳ございません。クーポンは存在しません');
        }

        // このクーポンがすでに使われたことがあるか確認（Orderに記録されているか）
        $shopCouponOrder = Order::where(function ($query) use ($shopCouponCode) {
            $query->where('coupon_code', $shopCouponCode)
                  ->orWhere('coupon_code', 'LIKE', "$shopCouponCode,%")
                  ->orWhere('coupon_code', 'LIKE', "%,$shopCouponCode")
                  ->orWhere('coupon_code', 'LIKE', "%,$shopCouponCode,%");
        })->first();

        if ($shopCouponOrder) {
            return back()->withMessage('申し訳ございません。このクーポンはすでに使用済みです。');
        }

        // カート内の商品を取得
        $cartItems = \Cart::session(auth()->id())->getContent();
        $targetProductId = (string) $shopCouponData->product_id;
        $matched = false;

        foreach ($cartItems as $item) {
            $cartProductId = (string) ($item->associatedModel->id ?? null);

            if ($cartProductId == $targetProductId) {

                // ✅ すでに同じ商品に対して別のクーポンが適用されていないか確認
                $hasOtherCouponApplied = collect($item->getConditions())
                ->contains(function ($condition) use ($shopCouponData)
                {
                    $attributes = $condition->getAttributes();

                    return isset($attributes['product_id'], $attributes['code']) &&
                           $attributes['product_id'] == $shopCouponData->product_id &&
                           $attributes['code'] != $shopCouponData->code;
                });

                if ($hasOtherCouponApplied) {
                    return redirect()->route('cart.index')
                        ->withMessage('すでにこの商品には別のクーポンが適用されています。一つの商品に対して一つのクーポンしか適用できません。価格を確認し再度適用して下さい');
                }


                $matched = true;

                // ✅ すでに同じクーポンが適用されていないか確認
                $alreadyApplied = collect($item->getConditions())
                    ->contains(function ($condition) use ($shopCouponData) {
                        return $condition->getName() === $shopCouponData->name;
                    });

                if ($alreadyApplied) {
                    return redirect()->route('cart.index')->withMessage('この商品にはすでにクーポンが適用されています。');
                }   

                // 既存の条件を削除してからクーポンを適用
                \Cart::session(auth()->id())->clearItemConditions($item->id);

                $condition = new \Darryldecode\Cart\CartCondition([
                    'name' => $shopCouponData->name,
                    'type' => $shopCouponData->type, // e.g. 'discount'
                    'target' => 'item',
                    'value' => $shopCouponData->value, // e.g. '-10%' or '-100'
                    'attributes' => [
                        'code' => $shopCouponData->code,
                        'coupon_id' => $shopCouponData->id,
                        'product_id' => $shopCouponData->product_id,
                    ],
                ]);

                \Cart::session(auth()->id())->addItemCondition((string)$item->id, $condition);

                // ここで割引を反映させるために価格を再計算
                $basePrice = $item->price;
                $discountTotal = 0;

                // 追加された条件を取得して割引を反映
                foreach ($item->getConditions() as $condition) {
                    $value = $condition->getValue();
                    if (is_string($value)) {
                        $discountTotal += floatval($value); // 例えば -60
                    }
                }

                $finalPrice = $basePrice + $discountTotal;

                // アイテムの価格を更新
                // \Cart::session(auth()->id())->update($item->id, [
                //     'price' => $finalPrice
                // ]);

                // 最終的な価格をアイテムに追加しておく
                $item->finalPrice = $finalPrice; // Bladeで表示できるようにfinalPriceを追加

                 // デバッグ出力
                // dd(\Cart::session(auth()->id())->getContent(), $item->id, $condition, $finalPrice);
            }
        }

        if (!$matched) {
            return back()->withMessage('注意！カート内にクーポン対象商品が見つかりませんでした。');
        }

        // セッションにクーポンコードを保存（重複しないように）
        $appliedCoupons = Session::get('applied_coupon_codes', []);
        if (!is_array($appliedCoupons)) {
            $appliedCoupons = explode(',', $appliedCoupons);
        }
        $appliedCoupons[] = $shopCouponData->code;
        $appliedCoupons = array_unique($appliedCoupons);

        Session::put('applied_coupon_codes', $appliedCoupons);

        // 🟡 カートアイテムに final_price を付けて Blade に渡す
        $cartItems = \Cart::session(auth()->id())->getContent()->map(function ($item) {
            $discount = 0;
            foreach ((array) $item->getConditions() as $condition) {
                $discount += floatval($condition->getValue());
            }

            // アイテムを clone してプロパティを追加（元のオブジェクトを壊さず）
            $item->final_price = $item->price + $discount;

            return $item;
        });
        $total = $cartItems->sum('final_price'); // 👈 ここで total を算出


        // return view('cart.index', [
        //     'cartItems' => $cartItems,
        //     'total' => $total, // 👈 Blade に渡す
        //     'message' => 'クーポンを適用しました。対象商品の金額が割引されているかご確認ください。'
        // ]);
        // 最後の return も `redirect()->route(...)`
        return redirect()->route('cart.index')->withMessage('クーポンを適用しました。クーポンを適用しました。対象商品の金額が割引されているかご確認ください。');
    }





}
