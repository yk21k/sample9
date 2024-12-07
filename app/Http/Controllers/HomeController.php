<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

use App\Models\Campaign;
use App\Models\Product;
use App\Models\Categories;
use App\Models\Attribute;
use App\Models\User;
use Illuminate\Support\Facades\Cookie;
use Carbon\Carbon;


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
        $today = Carbon::today();
        $capaign_objs = Campaign::where([
            
            ['status', 1],
            ['start_date', '<=', $today],
            ['end_date', '>=', $today]

        ])->get()->toArray();

        // dd($capaign_objs);
        // dd($capaingn_obj['items']);

        // $test = $capaingn_objs[0];
        // $test1 = $capaingn_objs[1];
        // dd($test['start_date'], $test1['start_date']);

        
        



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
        return response()->view('home', ['allProducts' => $products, 'product_attributes' => $product_attributes, 'categories' => $categories, 'capaign_objs' => $capaign_objs]);
    }



    public function testpage(Request $request)
    {
        return view('test.testpage');
    }

    public function privacy_policypage()
    {
        return view('policy.pri-polipage');
    }

    public function personal_information()
    {
        return view('information.personal-information');
    }

    public function terms_of_service()
    {
        return view('termofservice.terms_of_service');
    }

    public function listing_terms()
    {
        return view('listingterms.listing_terms');
    }




}
