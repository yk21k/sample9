<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Coupon;

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

        }
        $product_stocks->update(['stock' => $product_stocks->stock - $first_stock->quantity]);

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

    public function destroy($itemId)
    {   
        \Cart::session(auth()->id())->remove($itemId);
        return back();
    }

    public function update($rowId)
    {
        // 5 + 2 which results to 7 if updated relatively..
        \Cart::session(auth()->id())->update($rowId,[
            'quantity' => array(
              'relative' => false,
              'value' => request('quantity')
          ),
        ]);
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
