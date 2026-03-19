<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductViolation extends Model
{

    protected $fillable = [
        'product_id',
        'user_id',
        'violation_type',
        'reason',
        'severity'
    ];

}
