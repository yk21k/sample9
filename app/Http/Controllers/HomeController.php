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
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Fovorite;
use App\Models\FavoritesSaleRate;
use App\Models\FavoritesDisplay;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cookie;
use Carbon\Carbon;

use Illuminate\Support\Str;


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

        // 配列をコレクションに変換
        $capaign_objs = collect($capaign_objs);

        // 日付が重複した場合に、dicount_rate1 が高いものだけを抽出
        $uniqueCampaigns = $capaign_objs->sortByDesc('dicount_rate1')
            ->groupBy(function($item) {
                return $item['start_date']; // 配列なので、キーを配列のキーに合わせてアクセス
            })
            ->map(function ($group) {
                return $group[0]; // グループ内で最初の要素（dicount_rate1 が高いもの）を取得
            });

        $uniqueCampaigns = $uniqueCampaigns->values(); // インデックスをリセット

        $product_attributes = [];
        $product_attributes = Attribute::with('values')->get();
        // dd($product_attributes);

        $products = Product::take(20)->get();
        // dd($products);

        // $norm_products = Product::with('fovo_dises')->get();
        // dd($norm_products);

        $norm_products_pres = Product::with(['fovo_dises' => 
            function ($query) {
                $query->orderBy('norm_total', 'desc');
            }])->get();
        // dd($norm_products_pres);

        


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

        return response()->view('home', ['allProducts' => $products, 'product_attributes' => $product_attributes, 'categories' => $categories, 'capaign_objs' => $capaign_objs, 'holidays' => $holidays, 'extra_holidays' => $extra_holidays, 'norm_products_pres' => $norm_products_pres, 'uniqueCampaigns' => $uniqueCampaigns]);
        
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

    public function submit(Request $request)
    {
        
        dd('Hello');
        // POSTリクエストの処理
        return response()->json(['message' => 'データが正常に受け取られました！', 'data' => $request->all()]);
    }



}
