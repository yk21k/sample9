<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FinalOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id', 'shop_id', 'is_taxable',
        'subtotal', 'tax_amount', 'shipping_fee', 'total',
    ];

    public function items()
    {
        return $this->hasMany(FinalOrderItem::class);
    }

    
}
