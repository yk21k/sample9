<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Commition extends Model
{
    use HasFactory;

    protected $table = 'commitions'; // テーブル名

    protected $fillable = ['rate']; // rate がある前提

    /**
     * 現在のレートを取得
     *
     * @return Commition|null
     */
    public static function current()
    {
        // 単純に最新レコードを返す場合
        return self::orderByDesc('id')->first();
        
        // もし有効期間や日付条件がある場合はここで where 条件を追加
    }
    
    public function shop()
    {
        return $this->belongsTo(Shop::class, 'shop_id');
    }
}
