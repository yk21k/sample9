<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

use App\Models\Product;
use App\Models\Categories;
use App\Models\Attribute;
use App\Models\User;


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

        // dd($product_attrs);
        // dd(json_decode($product_attrs->product_attributes)->value);

        // dd($products);
        // dd($categories);
        return view('home', ['allProducts' => $products, 'categories' => $categories, 'product_attributes' => $product_attributes]);
    }

    public function testpage(Request $request)
    {
        return view('test.testpage');
    }




}
