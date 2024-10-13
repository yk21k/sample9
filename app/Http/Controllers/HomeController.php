<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

use App\Models\Product;
use App\Models\Categories;
use App\Models\Attribute;
use App\Models\User;
use Illuminate\Support\Facades\Cookie;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        // $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index(Request $request)
    {
        $product_attributes = [];
        $product_attributes = Attribute::with('values')->get();
        // dd($product_attributes);

        $products = Product::take(20)->get();
        $categories = Categories::whereNull('parent_id')->get();

        // dd($request->cookie());
        // $test = session()->all();
        // dd($test);

        // if($request->ajax){
        //     if(Cookie::get('data-bs-theme')=='dark'){
        //         Cookie::queue('data-bs-theme', 'light', 3);
        //         $theme = 'light';
                
        //     }
        //     else{
        //         Cookie::queue('data-bs-theme', 'dark', 3);
        //         $theme = 'dark';

        //     }

        //    // dd($values);
        // }
        
        // $theme = 'light';
        // Cookie::queue('data-bs-theme', $theme, 3);
        // return view('home');
        // dd($product_attrs);
        // dd(json_decode($product_attrs->product_attributes)->value);

        // dd($products);
        // dd($categories);    
        return response()->view('home', ['allProducts' => $products, 'product_attributes' => $product_attributes, 'categories' => $categories]);
    }



    public function testpage(Request $request)
    {
        return view('test.testpage');
    }

    public function privacy_policypage()
    {
        return view('policy.pri-polipage');
    }




}
