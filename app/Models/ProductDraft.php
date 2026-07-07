<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductDraft extends Model
{
    use HasFactory;

    /*
    |--------------------------------------------------------------------------
    | 一括代入
    |--------------------------------------------------------------------------
    */
    protected $fillable = [

        'shop_id',

        'product_id',

        'created_by',

        'original_created_by',

        'last_submitted_by',

        'name',

        'description',

        'price',

        'stock',

        'cover_img',

        'status',

        'owner_comment',

        'approved_at',

        'shipping_fee',

        'cover_img2',

        'cover_img3',

        'movie',

        'product_attributes',

        'movie_file',

        'image_ai_total_count',

        'image_ai_ok_count',

        'image_ai_ng_count',
    ];

    /*
    |--------------------------------------------------------------------------
    | casts
    |--------------------------------------------------------------------------
    */
    protected $casts = [

        'approved_at' => 'datetime',
        'product_attributes' => 'array',

    ];

    /*
    |--------------------------------------------------------------------------
    | 店舗
    |--------------------------------------------------------------------------
    */
    public function shop()
    {
        return $this->belongsTo(
            Shop::class,
            'shop_id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | 作成者
    |--------------------------------------------------------------------------
    */
    public function creator()
    {
        return $this->belongsTo(
            User::class,
            'created_by'
        );
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function product()
    {
        return $this->hasOne(
            Product::class,
            'draft_id'
        );
    }

    public function originalCreator()
    {
        return $this->belongsTo(
            User::class,
            'original_created_by'
        );
    }

    public function lastSubmitter()
    {
        return $this->belongsTo(
            User::class,
            'last_submitted_by'
        );
    }

    public function ownerReviewer()
    {
        return $this->belongsTo(
            User::class,
            'owner_reviewed_by'
        );
    }

    public function imageReviews()
    {
        return $this->hasMany(
            ProductImageReview::class,
            'draft_id'
        );
    }

}
