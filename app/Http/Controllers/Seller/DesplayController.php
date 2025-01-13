<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Shop;
use App\Models\Desplay;
use Auth;

class DesplayController extends Controller
{
    //
    public function index()
    {
        $check_desplay = Desplay::where('shop_id', auth()->user()->shop->id)->get(); 
        $check_desplay_count = Desplay::where('shop_id', auth()->user()->shop->id)->get()->count(); 
        // dd($check_desplay); 
        if($check_desplay_count == 0){
            return view('sellers.desplay.select_display', ["check_desplay_count" => 0]);
        }

        return view('sellers.desplay.select_display', ["check_desplay_count" => $check_desplay_count]);
        
    }

    public function saveSelect(Request $request)
    {
        $data = $request->all();
        // dd($data);
        $selectDesplay = new Desplay;

        $selectDesplay->shop_id = auth()->user()->shop->id;
        $selectDesplay->shop_name = $request->shop_name;
        $selectDesplay->desplay_id = $request->desplay_id;
        $selectDesplay->url = $request->url;
        $selectDesplay->desplay_phone = $request->desplay_phone;
        $selectDesplay->desplay_area = $request->desplay_area;
        $selectDesplay->desplay_real_store1 = $request->desplay_real_store1;
        $selectDesplay->desplay_real_address1 = $request->desplay_real_address1;
        $selectDesplay->desplay_real_store2 = $request->desplay_real_store2;
        $selectDesplay->desplay_real_address2 = $request->desplay_real_address2;
        $selectDesplay->save();

        return back();

    }

    public function deleteSelect()
    {
        $delpath = Desplay::where('shop_id', auth()->user()->shop->id)->first();
        $delpath_name = $delpath->shop_name;

        $path = public_path();
        // dd($path);
        // dd($delpath_name);
        
        @unlink($path. '/' . 'statics/shop_desplay_page'. $delpath_name .'.html');
        Desplay::where('shop_id', auth()->user()->shop->id)->delete();
        

        return back()->with('Shop page has been deselected.');
    }

}
