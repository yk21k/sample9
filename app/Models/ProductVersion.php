<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductVersion extends Model
{

    protected $fillable = [
        'product_id',
        'user_id',
        'before_data',
        'after_data',
        'change_type'
    ];

    protected $casts = [
        'before_data' => 'array',
        'after_data' => 'array'
    ];
}

