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
            'shippnng_fee' => $product->shippnng_fee,
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
                $item->shipping_fee = $item->associatedModel->shipping_fee;
            } else {
                $item->discounted_price = $item->price;
                $item->shipping_fee = $item->associatedModel->shipping_fee;

                $item->campaign = null;
            }

            $couponDiscount = 0;
            foreach ((array) $item->getConditions() as $condition) {
                $value = $condition->getValue();
                $couponDiscount += is_string($value) ? floatval($value) : 0;
                Log::debug("クーポン条件", [
                '値' => $condition->getValue(),
                '文字列か？' => is_string($condition->getValue()),
                ]);
            }
            // dd($item->associatedModel->shipping_fee);
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

               // ✅ ここで lowest_price が 0 未満なら削除
            if ($item->lowest_price < 0) {
                \Cart::session(auth()->id())->remove($item->id);


                Log::debug("商品が割引により0円未満のため削除されました", [
                    '商品ID' => $item->id,
                    '商品名' => $item->name,
                    '最終価格' => $item->lowest_price
                ]);

                return null; // return null にすると map の結果からも除外される
            }

            return $item;
        })->filter(); // null を除外
        // dd($item);



        // ✅ 4. 合計を再計算してセッションに保存
        $total = 0;

        $total = $discountedCarts->reduce(function ($carry, $item) {
            return $carry + ($item->lowest_price*$item->quantity);
        }, 0);
        // dd($item->associatedModel->shipping_fee);
        session(['cart_total' => $total]);




        // ✅ 5. ビューに渡す

        $cartItems = collect(session('cart_items'))->map(function ($item) {
            return (object) $item;
        });

        session(['cart_items' => $cartItems]);

        $cartItems = $discountedCarts;
        // dd($cartItems);
        
        // 削除された商品がある場合、セッションにメッセージ保存（flash ではなく put）
        if (!empty($removedItems)) {
            session()->put('removed_message', '以下の商品は割引により削除されました：' . implode(', ', $removedItems));
        }

       return view('cart.index', compact('cartItems', 'items', 'discountedCarts', 'total'));
    }




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


    public function checkout(Request $request)
    {
        $cartItems = \Cart::session(auth()->id())->getContent();
        $today = \Carbon\Carbon::today();

        // キャンペーン取得
        $campaigns = \App\Models\Campaign::where('status', 1)
            ->where('start_date', '<=', $today)
            ->where('end_date', '>=', $today)
            ->get();

        $discountedCarts = $cartItems->map(function ($item) use ($campaigns) {
            $shopId = $item->associatedModel?->getOriginal('shop_id');
            $shipping = (float) ($item->associatedModel->shipping_fee ?? 0);
            $productPrice = (float) $item->price;
            $quantity = $item->quantity;

            // --- キャンペーン価格（最初の1点用） ---
            $matchingCampaign = $campaigns->where('shop_id', $shopId)->sortByDesc('dicount_rate1')->first();
            $campaignPrice = $productPrice;
            if ($matchingCampaign) {
                $rate = $matchingCampaign->dicount_rate1;
                $campaignPrice = ceil($productPrice - ceil($productPrice * $rate));
            }

            // --- クーポン価格（最初の1点用） ---
            $couponDiscount = 0;
            foreach ((array) $item->getConditions() as $condition) {
                $value = $condition->getValue();
                if (is_string($value)) {
                    if (str_contains($value, '%')) {
                        $rate = floatval(str_replace(['-', '%'], '', $value)) / 100;
                        $couponDiscount += $productPrice * $rate;
                    } else {
                        $couponDiscount += abs(floatval($value));
                    }
                }
            }
            $couponPrice = $productPrice - $couponDiscount;

            // --- 最小価格を選択（0円未満を防ぐ） ---
            $lowestPrice = max(min($productPrice, $campaignPrice, $couponPrice), 0);

            // --- 商品小計 ---
            $productSubtotal = ($quantity > 1)
                ? $lowestPrice + $productPrice * ($quantity - 1)
                : $lowestPrice;

            // --- 配送料小計 ---
            $shippingTotal = $shipping * $quantity;

            // --- 合計 ---
            $total = $productSubtotal + $shippingTotal;

            $item->lowest_price = $lowestPrice;
            $item->shipping_fee = $shipping;
            $item->total_price = $total;

            // --- デバッグログ ---
            \Log::debug('カート商品の割引内訳', [
                '商品名' => $item->name,
                '商品価格' => $productPrice,
                'キャンペーン適用後' => $campaignPrice,
                'クーポン適用後' => $couponPrice,
                '最終適用価格（lowest_price）' => $lowestPrice,
                '数量' => $quantity,
                '商品小計（割引＋通常）' => $productSubtotal,
                '送料合計' => $shippingTotal,
                '合計金額' => $total
            ]);

            return $item;
        });

        // dd($discountedCarts);
        $cartTotal = $discountedCarts->sum('total_price');
        // dd($cartTotal);
        session()->put('total_and_shipping', $cartTotal);

        // 配送先情報など他の処理（省略）

        $cartItems = \Cart::session(auth()->id())->getContent();
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

        $cartProducts = \Cart::session(auth()->id())->getContent();

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
        if(!isset($setDeliPlaces)){
            $setDeliPlaces;
        }

        $setCart = \Cart::session(auth()->id())->isEmpty();
        if($setCart){
            return back()->withMessage('You cannot proceed to checkout. Please continue shopping');
        }else{
            return view('cart.checkout', compact('deliveryAddresses', 'setDeliPlaces', 'setCart', 'discountedCarts', 'cartTotal'));
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
        $totalAll = 0;
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

                // 最終的な価格をアイテムに追加しておく
                $item->finalPrice = $finalPrice; // Bladeで表示できるようにfinalPriceを追加

                 // デバッグ出力
            }
            $shippingFee = (float) ($item->associatedModel->shipping_fee ?? 0);
            $originalPrice = (float) $item->price + $shippingFee;
            $finalPrice = isset($item->final_price) ? (float) $item->final_price + $shippingFee : $originalPrice;
            $discountedPrice = isset($item->discounted_price) ? (float) $item->discounted_price + $shippingFee : $originalPrice;
            $lowestPrice = min($finalPrice, $discountedPrice);

            $quantity = $item->quantity;

            if ($lowestPrice < $originalPrice && $quantity > 1) {
                $totalPrice = $lowestPrice + $originalPrice * ($quantity - 1);
            } else {
                $totalPrice = $lowestPrice * $quantity;
            }

            $totalAll += $totalPrice;
        }
        // ✅ セッションに保存
        session(['total_and_shipping' => $totalAll]);

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

        return redirect()->route('cart.index')->withMessage('クーポンを適用しました。対象商品の金額が割引されているかご確認ください。クーポン適用後に該当商品がカートにない場合は、金額に満たないため削除されてます。');
    }
    
}
