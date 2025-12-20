<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PickupReservation extends Model
{
    use HasFactory;

    protected $fillable = [
        'pickup_slot_id',
        'order_id',
        'user_id',
        'status',
        'pickup_order_item_id', // ←これが必要！
    ];

    /**
     * 対応する受取スロット
     */
    public function slot()
    {
        return $this->belongsTo(PickupSlot::class, 'pickup_slot_id');
    }

    /**
     * 対応するユーザー
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * 対応する注文
     */
    public function order()
    {
        return $this->belongsTo(PickupOrder::class, 'order_id');
    }

    public function orderItem()
    {
        return $this->belongsTo(PickupOrderItem::class, 'pickup_order_item_id');
    }

    public function reservation()
    {
        return $this->hasOne(PickupReservation::class, 'pickup_order_item_id');
    }
}
    