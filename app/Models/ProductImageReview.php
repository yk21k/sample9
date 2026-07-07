<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductImageReview extends Model
{
    protected $fillable = [

        'product_id',

        'draft_id',

        'image_type',

        'image_path',

        'last_ai_hash',

        'status',

        'risk_score',

        'moderation_labels',

        'ai_provider',

        'reviewed_at',
    ];

    protected $casts = [

        'moderation_labels' => 'array',

        'reviewed_at' => 'datetime',
    ];

    public function product()
    {
        return $this->belongsTo(
            Product::class
        );
    }

    public function draft()
    {
        return $this->belongsTo(
            ProductDraft::class,
            'draft_id'
        );
    }
}