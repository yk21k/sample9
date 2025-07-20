<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Shop extends Model
{
    use HasFactory;

    protected $fillable=['name', 'is_active', 'is_draft', 'description', 'representative', 'location_1', 'location_2', 'telephone', 'email', 'identification_1', 'identification_2', 'identification_3','photo_1', 'photo_2', 'photo_3', 'photo_4', 'photo_5', 'photo_6', 'photo_7', 'file_1', 'file_2', 'file_3',  'file_4', 'manager', 'product_type', 'volume', 'note', 'license_expiry', 'person_1', 'person_2', 'person_3'];

    protected $casts = [
        'is_draft' => 'boolean',
    ];

    public function owner()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function products()
    {
        return $this->hasMany(Product::class, 'shop_id');
    }

    public function categories()
    {
        return $this->hasMany(Categories::class, 'shop_id');
    }

    public function shopCoupon()
    {
        return $this->hasMany(ShopCoupon::class, 'shop_id');
    }

    public function shopCampaign()
    {
        return $this->hasMany(Campaign::class, 'shop_id');
    }

    public function shopInq()
    {
        return $this->hasMany(Inquiries::class, 'shop_id');
    }

    public function desplay()
    {
        return $this->hasMany(Desplay::class, 'shop_id');
    }

    public function auction()
    {
        return $this->hasMany(Auction::class, 'shop_id');
    }

    public function commition()
    {
        return $this->hasMany(Commition::class, 'shop_id');
    }

    public function auction_order()
    {
        return $this->hasMany(AuctionOrder::class, 'shop_id');

    }

}
