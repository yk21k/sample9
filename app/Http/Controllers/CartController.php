<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Coupon;
use Illuminate\Support\Facades\DB;
use Session;

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

    public function checkout()
    {
        return view('cart.checkout');
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
}
