<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DailyQrCode extends Model
{
    // 保存するカラム
    protected $fillable = [
        'date',     // YYYY-MM-DD（unique）
        'token',    // QRに埋め込むトークン
        'created_at',    
        'updated_at',    
    ];

    // timestamps 使わない場合
    public $timestamps = false;

    // date を Carbon で扱う
    protected $casts = [
        'date' => 'date',
    ];
}
