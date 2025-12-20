<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PickupOtp extends Model
{
    use HasFactory;

    
    protected $fillable = [
        'user_id', 'order_id', 'code', 'expires_at', 'status', 'used_by_shop_id', 'used_at'
    ];


    // ✅ Carbonに自動変換
    protected $casts = [
        'expires_at' => 'datetime',
    ];

    protected $dates = ['expires_at', 'used_at'];

    // 関連
    public function order()
    {
        return $this->belongsTo(PickupOrder::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}


