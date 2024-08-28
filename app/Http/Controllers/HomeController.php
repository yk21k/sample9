<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Product;
use App\Models\Categories;

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
    public function index()
    {
        $products = Product::take(20)->get();
        $categories = Categories::whereNull('parent_id')->get();
        // dd($products);
        // dd($categories);
        return view('home', ['allProducts' => $products, 'categories' => $categories]);
    }


}
