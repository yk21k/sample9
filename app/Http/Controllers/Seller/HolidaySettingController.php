<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Calendar\HolidaySetting;
use Auth;

class HolidaySettingController extends Controller
{
    
    function form(){
        
        //取得
        $setting = HolidaySetting::where('shop_id', auth()->user()->shop->id)->firstOrNew();
        // dd($setting);
        

        return view("sellers.calendar.holiday_setting_form", [
            "setting" => $setting,
            "FLAG_OPEN" => HolidaySetting::OPEN,
            "FLAG_CLOSE" => HolidaySetting::CLOSE
        ]);
    }


    function update(Request $request){

        $param_holi = HolidaySetting::where('shop_id', auth()->user()->shop->id)->first();
        // dd($param_holi);
        if(empty($param_holi)){
            $holidaysetting = new HolidaySetting();
            $holidaysetting->shop_name = $request->shop_name;
            $holidaysetting->shop_id = $request->shop_id;
            
            $holidaysetting->flag_mon = $request->flag_mon;
            $holidaysetting->flag_tue = $request->flag_tue;
            $holidaysetting->flag_wed = $request->flag_wed;
            $holidaysetting->flag_thu = $request->flag_thu;
            $holidaysetting->flag_fri = $request->flag_fri;
            $holidaysetting->flag_sat = $request->flag_sat;
            $holidaysetting->flag_sun = $request->flag_sun;
            $holidaysetting->flag_holiday = $request->flag_holiday;
            // dd($holidaysetting);
            $holidaysetting->save();    
        }
        

        //取得
        $setting = HolidaySetting::where('shop_id', auth()->user()->shop->id)->firstOrCreate();
        // dd($request->all());
        //更新
        $setting->update($request->all());
        return redirect()
            ->action("App\Http\Controllers\Seller\HolidaySettingController@form")
            ->withStatus("保存しました");
    }
}
