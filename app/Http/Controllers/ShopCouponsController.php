<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\User;
use App\Models\ShopCoupon;
use Auth;
use Illuminate\Support\Str;

class ShopCouponsController extends Controller
{
    public function makeCouponPage(Request $request)
    {
        
        return view('sellers.orders.make_coupon_page');

    }

    public function makeCoupon(Request $request, User $user)
    {
        $sheets = $request->input('sheets');
        $expiry_date = $request->input('expiry_date');
        $products_id = $request->input('product_id');
        $values = $request->input('value');

        // dd(auth()->user()->shop->id);
        $product_shop_coupons = Product::where('shop_id', auth()->user()->shop->id)->get();
        // dd($product_shop_coupons);

        $request->validate([
            'sheets' => 'required|integer|min:1',
            'expiry_date' => 'required|date|after:today'
        ]);

        $product_shop_coupons_price = Product::where('id', $products_id)->first();
        // dd($product_shop_coupons_price->price);

        // dd($values*-1<$product_shop_coupons_price->price*0.2);
        // dd(abs($values)<$product_shop_coupons_price->price*0.2);

        if(abs($values)<$product_shop_coupons_price->price*0.2){
            return redirect()->route('order.make_coupon')->withMessage('クーポンは２割引以下で作成してください');    
        }

        $coupons = [];

        for($i = 0; $i < $sheets; $i++){
            
            $name = Str::upper(Str::random(10));
            $shop_id = auth()->user()->shop->id;
            $product_id = $products_id;
            $status = 1;
            $coupon_type = 1;
            $type = 'Discount';
            $value = $values;
            $code = Str::upper(Str::random(10));

            $coupons[] = ShopCoupon::create([
                'name' => $name,
                'shop_id' => $shop_id,
                'product_id' => $product_id,
                'expiry_date' => $expiry_date,
                'status' => $status,
                'coupon_type' => $coupon_type,
                'type' => $type,
                'value' => $value,
                'code' => $code,
            ]);

        }
        


        return redirect()->route('order.make_coupon')->withMessage('Coupons have been generated.');


    }
}
