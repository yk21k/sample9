<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Calendar\Form\CalendarFormView;
use App\Models\Calendar\ExtraHoliday;
use Auth;

class ExtraHolidaySettingController extends Controller
{
    public function form(Request $request){
        
        //クエリーのdateを受け取る
        $date = $request->input("date", "user_id");

        //dateがYYYY-MMの形式かどうか判定する
        if($date && strlen($date) == 7){
            $date = strtotime($date . "-01");
        }else{
            $date = null;
        }
        
        //取得出来ない時は現在(=今月)を指定する
        if(!$date)$date = time();
            
        //フォームを表示
        $calendar = new CalendarFormView($date);

        
        return view('sellers.calendar.extra_holiday_setting_form', [
            "calendar" => $calendar
        ]);
    }

    public function update(Request $request){

        $input = $request->get("extra_holiday");
        $input_user_name = $request->get("shop_name");
        $input_user_id = $request->get("shop_id");
        // dd($request);
        $ym = $request->input("ym");
        $date = $request->input("date");

        ExtraHoliday::updateExtraHolidayWithMonth($ym, $input, $input_user_name, $input_user_id);

        return redirect()
            ->action("App\Http\Controllers\Seller\ExtraHolidaySettingController@form", ["date" => $date])
            ->withStatus("保存しました");
    }
}
