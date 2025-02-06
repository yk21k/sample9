<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FavoritesSaleRate extends Model
{
    use HasFactory;

    protected $fillable = ['order_id','shop_id','product_id', 'norm_sale', 'norm_rate'];
}
