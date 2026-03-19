<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductReviewLog extends Model
{
    protected $fillable = [
        'product_id',
        'reviewer_id',
        'action',
        'comment'
    ];
}
