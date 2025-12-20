<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class PickupSecondaryOtp extends Model
{
    use HasFactory;

    protected $fillable = [
        'pickup_otp_id',
        'order_id',
        'user_id',
        'code',
        'expires_at',
        'status', // unused / used / expired
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    /**
     * 親の一次OTP（購入者が最初にログインしたOTP）とのリレーション
     */
    public function parentOtp()
    {
        return $this->belongsTo(PickupOtp::class, 'pickup_otp_id');
    }

    /**
     * 関連する注文
     */
    public function order()
    {
        return $this->belongsTo(PickupOrder::class, 'order_id');
    }

    /**
     * OTPが有効かチェック
     */
    public function isValid()
    {
        return $this->status === 'unused' && $this->expires_at->isFuture();
    }
}

