<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductVideoSocialPost extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_video_draft_id',
        'platform',
        'status',
        'visibility',
        'external_post_id',
        'published_at',
        'error_message',
    ];

    protected $casts = [
        'published_at' => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | Status
    |--------------------------------------------------------------------------
    */

    public const STATUS_PENDING = 'pending';

    public const STATUS_UPLOADING = 'uploading';

    public const STATUS_PROCESSING = 'processing';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_FAILED = 'failed';


    /*
    |--------------------------------------------------------------------------
    | Visibility
    |--------------------------------------------------------------------------
    */

    public const VISIBILITY_PUBLIC = 'public';

    public const VISIBILITY_PRIVATE = 'private';

    public const VISIBILITY_FRIENDS = 'friends';

    public const VISIBILITY_FOLLOWERS = 'followers';


    /*
    |--------------------------------------------------------------------------
    | Platform
    |--------------------------------------------------------------------------
    */

    public const PLATFORM_YOUTUBE = 'youtube';

    public const PLATFORM_INSTAGRAM = 'instagram';

    public const PLATFORM_TIKTOK = 'tiktok';

    public const PLATFORM_FACEBOOK = 'facebook';

    public const PLATFORM_X = 'x';


    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */

    public function productVideoDraft(): BelongsTo
    {
        return $this->belongsTo(
            ProductVideoDraft::class,
            'product_video_draft_id'
        );
    }
}