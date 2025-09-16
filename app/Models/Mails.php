<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mails extends Model
{
    use HasFactory;

    protected $table = 'mails';

    protected $fillable = [
        'user_id',
        'shop_id',
        'mail',
        'template',
        'purpose',
        'product_id',
        'coupon_id',
        'campaign_id',
        'order_number',
        'sub_order_id',
        'subject',
        'body',
        'items_with_pricing',
    ];
    
    protected $casts = [
        'items_with_pricing' => 'array', // JSON を自動で配列に変換
    ];

    // キャメルケースでアクセスしたい場合
    public function getItemsWithPricingAttribute()
    {
        return $this->items_with_pricing ?? [];
    }

    public function setItemsWithPricingAttribute($value)
    {
        $this->attributes['items_with_pricing'] = json_encode($value);
    }

    public function forMailUser()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function forMailCoupon()
    {
        return $this->belongsTo(Coupon::class, 'coupon_id');
    }

    public function forMailCampaign()
    {
        return $this->belongsTo(Campaign::class, 'campaign_id');
    }

    public function forMailReceipt()
    {
        return $this->belongsTo(Campaign::class, 'campaign_id');
    }
}
