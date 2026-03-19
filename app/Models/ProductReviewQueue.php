<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductReviewQueue extends Model
{
    protected $fillable = [
        'product_id',
        'status',
        'reviewer_id',
        'risk_score',
        'comment',
        'review_started_at',
        'reviewed_at'
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
