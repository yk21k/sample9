<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

use App\Models\Calendar\HolidaySetting;
use App\Models\Calendar\ExtraHoliday;
use App\Models\Campaign;
use App\Models\Product;
use App\Models\Categories;
use App\Models\Attribute;
use App\Models\User;
use App\Models\Shop;
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

        $product_attributes = [];
        $product_attributes = Attribute::with('values')->get();
        // dd($product_attributes);

        $products = Product::take(20)->get();
        // dd($products);
        $categories = Categories::whereNull('parent_id')->get();

        $week = array( "flag_sun", "flag_mon", "flag_tue", "flag_wed", "flag_thu", "flag_fri", "flag_sat" );
        $holidays = HolidaySetting::where($week[date("w")], '!=' , "1")->get('shop_name');


        $holidays_count = HolidaySetting::where($week[date("w")], '=' , 2)->get('shop_name')->count();

        // dd($holidays);

        // dd($holidays_dex->shop_name);

        $d = now();

        $para_d = $d->format('Ymd');

        $extra_holidays = ExtraHoliday::where('date_key', $para_d)->get();

        $holidays_array = HolidaySetting::where($week[date("w")], '!=' , "1")->get('shop_name')->toArray();

        $extra_holidays_dex = ExtraHoliday::where('date_key', $para_d)->get('shop_name')->toArray();

        // dd($holidays_array);
        // dd($extra_holidays_dex);

        $extra_holidays_count = ExtraHoliday::where('date_key', $para_d)->get('shop_name')->count();

        // $test = ExtraHoliday::where('shop_name', '!=', $holidays_dex->shop_name)->get();
        // $test = $holidays->filter(function($extra_holidays_dex) => $extra_holidays_dex['shopname'] === 'nagano');
        
        // $test = array_filter($holidays_array, $extra_holidays_dex);    
        // dd($test);
        // dd($holidays_array);

        // dd($extra_holidays_array);

        // $holidays_merge = array_merge($holidays_array, $extra_holidays_array);
        // dd($holidays_merge);

        

        // dd($holidays_merge);----

        // dd($holidays, $extra_holidays);

        // dd($extra_holidays);
        
        // dd($week[date("w")]);
        // echo print_r($holidays->user_id) ;die;

        // $extra_holidays = ExtraHoliday::get()->all();
        // $extra_holidays_sets = $extra_holidays_sets->user_id->owner();

        // dd($capaign_objs);
        // dd($capaingn_obj['items']);

        // $test = $capaingn_objs[0];
        // $test1 = $capaingn_objs[1];
        // dd($test['start_date'], $test1['start_date']);
        

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
        return response()->view('home', ['allProducts' => $products, 'product_attributes' => $product_attributes, 'categories' => $categories, 'capaign_objs' => $capaign_objs, 'holidays' => $holidays, 'extra_holidays' => $extra_holidays]);
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
