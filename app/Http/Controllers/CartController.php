<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Coupon;
use App\Models\ShopCoupon;
use App\Models\Order;
use App\Models\DeliveryAddress;
use Illuminate\Support\Facades\View;

use Illuminate\Support\Facades\DB;
use Session;
use Auth;

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
            'id' => $product->id,
            'name' => $product->name,
            'price' => $product->price,
            'quantity' => 1,
            'attributes' => array(),
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

    public function index()
    {   
        $cartItems = \Cart::session(auth()->id())->getContent();
        // dd($cartItems);
        // dd($cartItems->getOriginal('shop_id'));
        // dd($cartItems->getChanges('shop_id'));
        // dd($cartItems->getCachingIterator());

        $items = \Cart::getContent();
        
        // dd($items);
        return view('cart.index', compact('cartItems', 'items'));
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

        // \Cart::session(auth()->id())->update($rowId,[
        //     'quantity' => request('quantity')
        // ]);

        // \Cart::session(auth()->id())->update($rowId,[
        //     'quantity' => array(
        //       'relative' => true,
        //       'value' => request('quantity')
        //   ),
        // ]);

        // \Cart::session(auth()->id())->update($itemId,[
        //     'quantity' => array(
        //       'relative' => true,
        //       'value' => request('quantity')
        //   ),
        // ]);


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
        $deliveryAddresses = DeliveryAddress::where('user_id', Auth::user()->id)->get();
        // dd($deliveryAddresses);
        

        $setDeliPlaces = DeliveryAddress::setDeliPlaces();
        // dd($setDeliPlaces);
        if(!empty($setDeliPlaces)){
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

    public function applyShopCoupon()
    {
        
        
        $shopCouponCode = request('code');
        // dd($shopCouponCode);

        $shopCouponData = ShopCoupon::where('code', $shopCouponCode)->first();
        // dd($shopCouponData);

        if(!$shopCouponData) {
            return back()->withMessage('Sorry! Coupon does not exist');
        }

        $shopCouponOrder = Order::where('coupon_code', $shopCouponCode)->first();
        
        if($shopCouponOrder) {
            return back()->withMessage('Sorry! This coupon cannot be used as it has already been paid.');
        }    

        //coupon logic
        // $saleCondition = new \Darryldecode\Cart\CartCondition(array(
        //     'name' => $shopCouponData->name,
        //     'type' => $shopCouponData->type,
        //     'value' => $shopCouponData->value,
        // ));
        // dd($condition);

        $cartItems = \Cart::session(auth()->id())->getContent();
        $items = \Cart::getContent();
        // dd($cartItems);
        // dd($items);
        // dd($shopCouponData->product_shop_coupon->name);

        $productID = $shopCouponData->product_id;
        \Cart::clearItemConditions($productID);
        $coupon101 = new \Darryldecode\Cart\CartCondition(array(
            'name' => $shopCouponData->name,
            'code' => $shopCouponData->code,  
            'type' => $shopCouponData->type,
            'value' => $shopCouponData->value,
        ));

        \Cart::addItemCondition($productID, $coupon101);

        Session::put('coupon101', print_r($shopCouponCode, true));
        
           

        // dd(\Cart::session(auth()->id())->getContent());
        // dd(\Cart::session(auth()->id())->getConditions());
        // $cartItems = array(
        //     'id' => $shopCouponData->product_id,
        //     'name' => $shopCouponData->product_shop_coupon->name,
        //     'price' => $shopCouponData->product_shop_coupon->price,
        //     'quantity' => 1,
        //     'conditions' => $saleCondition
        // );

        // \Cart::add($cartItems);
        // \Cart::add($saleCondition);
        // \Cart::session(auth()->id())->condition($saleCondition);
        // \Cart::condition($saleCondition);
        // \Cart::addItemCondition($items, $saleCondition);

        // \Cart::session(auth()->id())->condition($saleCondition); // for a speicifc user's cart

        return back()->withMessage('coupon applied');

    }
}
