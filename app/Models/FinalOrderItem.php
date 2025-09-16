<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FinalOrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'final_order_id', 'product_id', 'product_name',
        'quantity', 'unit_price', 'tax_rate', 'tax_amount', 'subtotal',
    ];

    public function finalOrder()
    {
        return $this->belongsTo(FinalOrder::class);
    }
    
}
