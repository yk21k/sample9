<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Shop;

class ShopSettingController extends Controller
{
    public function index()
    {
        $shop_settings = Shop::where('user_id', auth()->id())->get();

        return view('sellers.shop.shop_setting', compact(['shop_settings']));
    }

    public function shopUpdate()
    {

    }
}
