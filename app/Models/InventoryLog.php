<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryLog extends Model
{
    use HasFactory;

    protected $fillable = ['product_id', 'date', 'stock', 'shop_id'];

    public function product()
    {
        return $this->belongsTo(Product::class); // 商品とのリレーション
    }
}
