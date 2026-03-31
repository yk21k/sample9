<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductReviewQueue extends Model
{
    protected $fillable = [
        'product_id',
        'user_id',
        'status',
        'requested_at',
        'review_started_at',
        'reviewed_at',
        'reviewer_id',
        'fix_fields',
        'risk_score',
        'comment',
        'ai_result',
        'ai_score',
        'ai_status',
        'ai_checked_at',
    ];

    protected $casts = [
        'fix_fields' => 'array',
        'ai_result' => 'array',
        'ai_checked_at' => 'datetime',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class,'reviewer_id');
    }
}
