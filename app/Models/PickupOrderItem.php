<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class PickupOrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'pickup_order_id',
        'product_id',
        'quantity',
        'price',
        'pickup_date',
        'pickup_time',
        'type',
        'status',
        'person_in_charge',
        'received_at',
    ];

    protected $casts = [
        'pickup_date' => 'date', 
        'received_at' => 'datetime',    
    ];

    /**
     * PickupOrderItem は PickupOrder に属する
     */
    public function order()
    {
        return $this->belongsTo(PickupOrder::class, 'pickup_order_id');
    }

    /**
     * 商品情報を取得
     */
    public function product()
    {
        return $this->belongsTo(PickupProduct::class, 'product_id');
    }

    public function getPickupDatetimeAttribute()
    {
        // pickup_date または pickup_time が null の場合は null を返す
        if (empty($this->pickup_date) || empty($this->pickup_time)) {
            return null;
        }

        // pickup_date と pickup_time を組み合わせて Carbon に変換
        try {
            // pickup_date は date 型なので Carbon オブジェクトになっている前提
            // pickup_time は HH:MM:SS の文字列
            return \Carbon\Carbon::createFromFormat(
                'Y-m-d H:i:s',
                $this->pickup_date->format('Y-m-d') . ' ' . $this->pickup_time
            );
        } catch (\Exception $e) {
            return null;
        }
    }

    public function pickupSlot()
    {
        return $this->belongsTo(PickupSlot::class);
    }

    public function slot()
    {
        return $this->belongsTo(\App\Models\PickupSlot::class, 'pickup_slot_id');
    }
}
