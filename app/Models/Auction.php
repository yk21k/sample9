<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Auction extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'spot_price',
        'suggested_price',
        'description',
        'payment_status',
        'payment_method',
        'payment_at',
        'winner_user_id',
        'shipping_fullname',
        'shipping_address',
        'shipping_city',
        'shipping_state',
        'shipping_zipcode',
        'shipping_phone',
        'event_ended',
        'final_price',
        'shipping_company',
        'reception_number',
        // その他必要な項目を追加
    ];
    
    public function shop()
    {
        return $this->belongsTo(Shop::class, 'shop_id');
    }

    public function categories()
    {
        return $this->belongsToMany(Categories::class, 'category_auction', 'auction_id', 'category_id');
    }

    public function winner()
    {
        return $this->belongsTo(User::class, 'winner_user_id');
    }

    public function bids()
    {
        return $this->hasMany(\App\Models\Bid::class);
    }

}
