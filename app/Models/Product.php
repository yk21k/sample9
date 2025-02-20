<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = ['campaigns_rate1'];
    protected $guarded = ['id'];


    // protected $casts = 
    // [
    //     'product_attributes'=>'array'
    // ];

    protected static function booted()
    {
        static::saving(function($product){

            // dd($request);
            $product->product_attributes = json_encode(request('product_attributes'));
        });
    }

    public function shop()
    {
        return $this->belongsTo(Shop::class, 'shop_id');
    }

    public function category()
    {
        return $this->belongsTo(Categories::class, 'category_id');
    }

    public function categories()
    {
        return $this->belongsToMany(Categories::class, 'category_product', 'product_id', 'categories_id');
    }

    public function fovo_dises()
    {
        return $this->hasMany(FavoritesDisplay::class);    
    }

    public function user_favo()
    {
        return $this->hasMany(Fovorite::class);    
    }


 






}
