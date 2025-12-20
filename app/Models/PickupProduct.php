<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PickupProduct extends Model
{
    use HasFactory;

    protected $fillable = ['shop_id', 'name', 'status', 'description', 'price', 'stock'];

    public function slots()
    {
        return $this->hasMany(PickupSlot::class);
    }

    public function shop()
    {
        return $this->belongsTo(Shop::class, 'shop_id');
    }

    public function shop_location()
    {
        return $this->belongsTo(Shop::class, 'shop_id')->select('id', 'name', 'invoice_number');    
    }

    public function pick_location()
    {
        // shop_id が共通の pickup location を取得
        return $this->hasOne(PickupLocation::class, 'shop_id', 'shop_id')->select('id', 'shop_id', 'name', 'address', 'youtube_url');
    }

    public function decreaseStock(int $quantity): bool
    {
        return \DB::transaction(function () use ($quantity) {
            $this->refresh();
            $this->lockForUpdate();

            if ($this->stock < $quantity) {
                return false;
            }

            $this->stock -= $quantity;
            $this->save();
            return true;
        });
    }



}

