<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PriceHistory extends Model
{
    use HasFactory;

    protected $fillable = ['product_id', 'price', 'shop_id'];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
