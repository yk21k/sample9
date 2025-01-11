<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

use Illuminate\Http\Request;
use App\Models\ShopProf;
use App\Models\Product;

class ShopProfController extends Controller
{
    public function index(){

        $shop_dataes = ShopProf::get();
        $shop_products = Product::get();
        // dd($shop_dataes);
        // dd($shop_products);
        return view('company_shop.profile_shop')->with(compact('shop_dataes', 'shop_products'));
    }
}
