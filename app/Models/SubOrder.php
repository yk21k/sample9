<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubOrder extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'transferred_at' => 'datetime',
    ];

    public function items()
    {
        return $this->belongsToMany(Product::class, 'sub_order_items', 'sub_order_id', 'product_id')
                    ->withPivot('quantity', 'price', 'user_id');
    }


    public function order()
    {

        return $this->belongsTo(Order::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function seller_user()
    {
        return $this->belongsTo(User::class);
    }

    public function arrivalUser()//SubOrderObserverで利用
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function arrivalReport()
    {
        return $this->hasOne(SubOrdersArrivalReport::class);
    }

    public function subOrder_item()
    {
       return $this->hasMany(SubOrderItem::class,  'sub_order_items', 'sub_order_id', 'product_id', 'quantity');
    }

    public function shopCoupons()
    {
        return $this->belongsToMany(ShopCoupon::class, 'shop_coupon_sub_order', 'sub_order_id', 'shop_coupon_id');
    }

    
}
