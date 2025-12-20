<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PickupOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'product_id',
        'quantity',
        'price',
        'payment_intent_id',
        'status', // 0=未受取, 1=受取済みなど
    ];

    // 例えば、ユーザーや商品とのリレーション
    public function user() {
        return $this->belongsTo(User::class);
    }

    public function product() {
        return $this->belongsTo(PickupProduct::class, 'product_id');
    }

    public function pickupLocation() {
        return $this->belongsTo(PickupLocation::class, 'pickup_location_id');
    }

    public function items()
    {
        return $this->hasMany(PickupOrderItem::class, 'pickup_order_id');
    }

    // public function otp()
    // {
    //     return $this->hasOne(PickupOtp::class);
    // }

    public function otp()
    {
        return $this->hasOne(PickupOtp::class, 'order_id');
    }

    public function reservations()
    {
        return $this->hasMany(PickupReservation::class, 'order_id');
    }

}
