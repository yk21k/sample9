<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductReview extends Model
{

    protected $fillable = [
        'product_id',
        'reviewer_id',
        'status',
        'comment',
        'ai_result',
        'ai_score',
        'ai_status',
        'ai_checked_at',
    ];

    protected $casts = [
        'ai_result' => 'array',
        'ai_checked_at' => 'datetime',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

}
