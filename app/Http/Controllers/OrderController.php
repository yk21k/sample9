<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreOrderRequest;
use App\Http\Requests\UpdateOrderRequest;

use App\Models\Order;
use App\Models\Product;
use App\Models\OrderItem;
use App\Models\SubOrder;
use Session;


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
    public function store(StoreOrderRequest $request, Product $product)
    {
        // dd($request->all());

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

        if(!$request->has('billing_fullname')) {
            $order->billing_fullname = $request->input('shipping_fullname');
            $order->billing_state = $request->input('shipping_state');
            $order->billing_city = $request->input('shipping_city');
            $order->billing_address = $request->input('shipping_address');
            $order->billing_phone = $request->input('shipping_phone');
            $order->billing_zipcode = $request->input('shipping_zipcode');
        }else {
            $order->billing_fullname = $request->input('billing_fullname');
            $order->billing_state = $request->input('billing_state');
            $order->billing_city = $request->input('billing_city');
            $order->billing_address = $request->input('billing_address');
            $order->billing_phone = $request->input('billing_phone');
            $order->billing_zipcode = $request->input('billing_zipcode');
        }
        // dd(Session::get('coupon101'));

        $order->coupon_code = Session::get('coupon101');

        $order->grand_total = \Cart::session(auth()->id())->getTotal();
        $order->item_count = \Cart::session(auth()->id())->getContent()->count();

        $order->user_id = auth()->id();
        // $order->user_id = $request->user()->id;

        if (request('payment_method') == 'paypal') {
            $order->payment_method = 'paypal';
        }

        $order->save();

        // save order items

        $cartItems = \Cart::session(auth()->id())->getContent();

        // dd($cartItems);

        foreach ($cartItems as $item) {
            $order->items()->attach($item->id, ['price' => $item->price, 'quantity' => $item->quantity]);

            $update_stock = new Product;
            $update_stock->where('id', '=', $item->id)->decrement('stock', $item->quantity);
            // $order_goods = \Cart::session(auth()->id())->getContent($item->id);
            // $order_goods = $item->quantity;
            // $order_goods = $order->items()->$item->quantity;
            // dd($order->items()->quantity);
            // dd($cartItems);
            // dd($product_stocks->stock);
            // dd($item->quantity);

        }
        // dd($order->items());
        // dd($request->qty);
        

        // $product_stocks = Product::find($item->id);
            // Product::where('id', '=', $item->id)->update(['stock' => $product_stocks->stock - $first_stock->quantity]);
        // Product::where('id', '=', $product->id)->update(['stock' => $product_stocks->stock - $first_stock->quantity]);

        // dd($cartItems->id);

        // $first_stocks = \Cart::session(auth()->id())->getContent($product->id);
        // dd($first_stocks);

        // foreach($first_stocks as $first_stock)
        // {
        //     $first_stock->quantity;
        //     $product_stocks = Product::find($first_stock->id);

        // }
        // dd($first_stock->quantity, $product_stocks->stock);

        
        // $test =[];
        // $test = $order->items();
        // dd($test);
        // dd($order_goods);
        // dd($order_goods->id);
        // dd($product_stocks);

        // Product::where('id', '=', $product->id)->update(['stock' => $product_stocks->stock - $first_stock->quantity]);

        // $product_stocks = Product::find($item->id);
        // $cart_quantity = OrderItem::where('order_id', '=', $order->id)->get();
        // dd($product_stocks->stock, $cart_quantity->product());

        $order->generateSubOrders();
        $order->generateFavoritesSalesRate();
        $order->generateFavoritesDisplay();

        // payment
        if (request('payment_method') == 'paypal') {
            
            // redirect pp
            return redirect()->route('paypal.checkout', $order->id);
        }

        // empty cart

        \Cart::session(auth()->id())->clear();

        // send email to customer

        // take user to thank you

        return redirect()->route('home')->withMessage('Order has been placed'); 

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
