<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Auth;

class Order extends Model
{
    use HasFactory;

    protected $guarded = [];
    
    public function items()
    {
        return $this->belongsToMany(Product::class, 'order_items', 'order_id', 'product_id')->withPivot('quantity', 'price');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getShippingFullAddressAttribute()
    {

        return  $this->shipping_fullname."<br>".$this->shipping_address . ', ' . $this->shipping_city . ', ' . $this->shipping_state . ', ' . $this->shipping_zipcode . "<br> phone: " . $this->shipping_phone;
    }

    public function order_item()
    {
       return $this->hasMany(OrderItem::class,  'order_items', 'order_id', 'product_id', 'quantity');
    }

    public function subOrders()
    {
        return $this->hasMany(SubOrder::class);
    }

    public function generateSubOrders()
    {
        $orderItems = $this->items;
        // dd($this);
        foreach ($orderItems->groupBy('shop_id') as $shopId => $products) {
            $shop = Shop::find($shopId);

            $suborder = $this->subOrders()->create([
                'order_id'=> $this->id,
                'seller_id'=> $shop->user_id ?? 1,
                'user_id'=> Auth::user()->id,
                'grand_total'=> $products->sum('pivot.price'),
                'item_count'=> $products->count(),
                'coupon_code'=> $this->coupon_code,

            ]);

            // dd($suborder);

            foreach($products as $product) {
                $suborder->items()->attach($product->id, ['price' => $product->pivot->price, 'quantity' => $product->pivot->quantity]);
            }

        }
    }



}
