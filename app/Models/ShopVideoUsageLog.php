<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShopVideoUsageLog extends Model
{
    protected $fillable = [

        'shop_id',

        'video_id',

        'usage_type',

        'usage_date',

    ];

    protected $casts = [

        'usage_date' => 'date',

    ];

    /*
    |--------------------------------------------------------------------------
    | Usage Type
    |--------------------------------------------------------------------------
    */

    public const TYPE_VIDEO_PROCESSING
        = 'video_processing';

    /*
    |--------------------------------------------------------------------------
    | Shop
    |--------------------------------------------------------------------------
    */

    public function shop(): BelongsTo
    {
        return $this->belongsTo(
            Shop::class
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Video
    |--------------------------------------------------------------------------
    */

    public function video(): BelongsTo
    {
        return $this->belongsTo(
            ShopVideoDraft::class,
            'video_id'
        );
    }
}