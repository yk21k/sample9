<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PickupSlot extends Model
{
    use HasFactory;

    protected $fillable = [
        'pickup_product_id',
        'date', 'start_time',
        'end_time',
        'capacity',
        'remaining_capacity',
        'is_active',
        'note'
    ];
    
    protected $casts = [
        'date' => 'date', // DATE型なら
        // 'start_time' => 'datetime:H:i:s' // もし必要なら
    ];

    public function product()
    {
        return $this->belongsTo(PickupProduct::class, 'pickup_product_id');
    }


    public function reservations()
    {
        return $this->hasMany(PickupReservation::class);
    }

    // 残枠を計算する便利メソッド
    public function available()
    {
        return $this->remaining_capacity - $this->reservations()->count();
    }

    /**
     * 🧾 残り枠（capacity）を1減らす
     * 
     * @throws \Exception
     */
    public function decrementCapacity(int $quantity): bool
    {
        return \DB::transaction(function () use ($quantity) {
            $this->refresh();
            $this->lockForUpdate();

            if ($this->remaining_capacity < $quantity) {
                return false;
            }

            $this->remaining_capacity -= $quantity;
            $this->save();
            return true;
        });
    }



    /**
     * 🧩 予約キャンセルなどで枠を戻す
     */
    public function incrementCapacity(int $amount = 1)
    {
        DB::transaction(function () use ($amount) {
            $slot = self::where('id', $this->id)->lockForUpdate()->first();
            $slot->capacity += $amount;
            $slot->save();

            $this->capacity = $slot->capacity;
        });

        return $this;
    }

}

