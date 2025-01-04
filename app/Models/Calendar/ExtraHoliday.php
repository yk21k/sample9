<?php

namespace App\Models\Calendar;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Auth;
use App\Models\Shop;

class ExtraHoliday extends Model
{
    use HasFactory;
    const OPEN = 1;
    const CLOSE = 2;
    protected $table = "extra_holiday";
    
    protected $fillable = [
        "date_flag",
        "comment"
    ];
    function isClose(){
        return $this->date_flag == ExtraHoliday::CLOSE;
    }
    function isOpen(){
        return $this->date_flag == ExtraHoliday::OPEN;
    }
    /**
     * 指定した月の臨時営業・休業を取得する
     * @return ExtraHoliday[]
     */
    public static function getExtraHolidayWithMonth($ym){
        return ExtraHoliday::where('shop_id', auth()->user()->shop->id)->where("date_key", 'like', $ym . '%')
            ->get()->keyBy("date_key");
    }

    /**
     * 一括で更新する
     */
    public static function updateExtraHolidayWithMonth($ym, $input , $input_user_name, $input_user_id){
        
        $extreaHolidays = self::getExtraHolidayWithMonth($ym);
        // dd($extreaHolidays);
        // dd($input_user);
        // dd($input);
        foreach($input as $date_key => $array){
            
            $date_key_count = self::getExtraHolidayWithMonth($ym)->count();

            if($date_key_count > 0 && $input_user_id == auth()->user()->shop->id){  //既に作成済の場合
                
                // dd($extreaHolidays);
                if(isset($extreaHolidays[$date_key])){
                    $extraHoliday = $extreaHolidays[$date_key];

                    $extraHoliday->fill($array);

                    //CloseかOpen指定の場合は上書き
                    if($extraHoliday->isClose() || $extraHoliday->isOpen()){
                        $extraHoliday->save();
                    
                    //指定なしを選択している場合は削除
                    }else{
                        $extraHoliday->delete();
                    }
                    continue;    
                }
                
            }

            $extraHoliday = new ExtraHoliday();
            $extraHoliday->date_key = $date_key;
            $extraHoliday->shop_name = $input_user_name;
            $extraHoliday->shop_id = $input_user_id;
            $extraHoliday->fill($array);
            // dd($extraHoliday);
            //CloseかOpen指定の場合は保存
            if($extraHoliday->isClose() || $extraHoliday->isOpen()){
                $extraHoliday->save();
            }
        }
    }

    public function shop()
    {
        return $this->belongsTo(Shop::class, 'shop_id');
    }
}
