<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShopCoupon extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function shop()
    {
        return $this->belongsTo(Shop::class, 'shop_id');
    }

    public function product_shop_coupon()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function subOrders()
    {
        return $this->belongsToMany(SubOrder::class, 'shop_coupon_sub_order', 'shop_coupon_id', 'sub_order_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }


}
