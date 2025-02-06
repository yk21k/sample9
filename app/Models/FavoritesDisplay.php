<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;


class FavoritesDisplay extends Model
{
    use HasFactory;
    protected $fillable = ['order_id','shop_id','product_id', 'fovorite_id', 'norm_sale', 'norm_rate'];

    public function for_favo_displaes()
    {
        return $this->belongsTo(Product::class);
    }
    
}
