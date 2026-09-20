<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;


class ProductVideoDraft extends Model
{
    /*
    |--------------------------------------------------------------------------
    | Table
    |--------------------------------------------------------------------------
    */

    protected $table = 'product_video_drafts';


    /*
    |--------------------------------------------------------------------------
    | Fillable
    |--------------------------------------------------------------------------
    */

    protected $fillable = [

        /*
        |--------------------------------------------------------------------------
        | Product
        |--------------------------------------------------------------------------
        */

        'product_id',

        /*
        |--------------------------------------------------------------------------
        | Shop
        |--------------------------------------------------------------------------
        */

        'shop_id',

        /*
        |--------------------------------------------------------------------------
        | 基本
        |--------------------------------------------------------------------------
        */

        'title',

        'description',

        /*
        |--------------------------------------------------------------------------
        | 動画
        |--------------------------------------------------------------------------
        */

        'original_movie',

        'processed_movie',

        'thumbnail',

        'preview_movie',

        'duration',

        'file_size',

        'mime_type',

        /*
        |--------------------------------------------------------------------------
        | AI
        |--------------------------------------------------------------------------
        */

        'ai_status',

        'ai_provider',

        'rekognition_job_id',

        'risk_score',

        'moderation_labels',

        'ai_reviewed_at',

        /*
        |--------------------------------------------------------------------------
        | 動画加工
        |--------------------------------------------------------------------------
        */

        'process_status',

        'processed_at',

        'edit_mode',

        'bgm_id',

        /*
        |--------------------------------------------------------------------------
        | Preview
        |--------------------------------------------------------------------------
        */

        'preview_status',

        'preview_url',

        'preview_generated_at',

        /*
        |--------------------------------------------------------------------------
        | 出品者確認
        |--------------------------------------------------------------------------
        */

        'seller_review_status',

        'seller_reviewed_at',

        'seller_reviewed_by',

        'seller_review_comment',

        /*
        |--------------------------------------------------------------------------
        | 管理者審査
        |--------------------------------------------------------------------------
        */

        'review_status',

        'reviewed_at',

        'reviewed_by',

        'review_reject_reason',

        /*
        |--------------------------------------------------------------------------
        | YouTube
        |--------------------------------------------------------------------------
        */

        'youtube_status',

        'youtube_video_id',

        'youtube_uploaded_at',

        /*
        |--------------------------------------------------------------------------
        | Workflow
        |--------------------------------------------------------------------------
        */

        'workflow_stage',

        /*
        |--------------------------------------------------------------------------
        | Product公開
        |--------------------------------------------------------------------------
        */


        'is_active',

        'created_by',

    ];


    /*
    |--------------------------------------------------------------------------
    | Casts
    |--------------------------------------------------------------------------
    */

    protected $casts = [

        'moderation_labels' => 'array',

        'ai_reviewed_at' => 'datetime',

        'processed_at' => 'datetime',

        'preview_generated_at' => 'datetime',

        'seller_reviewed_at' => 'datetime',

        'reviewed_at' => 'datetime',

        'youtube_uploaded_at' => 'datetime',

        'is_active' => 'boolean',

    ];


    /*
    |--------------------------------------------------------------------------
    | AI Status
    |--------------------------------------------------------------------------
    */

    public const AI_PENDING = 'pending';

    public const AI_PROCESSING = 'processing';

    public const AI_APPROVED = 'approved';

    public const AI_WARNING = 'warning';

    public const AI_REJECTED = 'rejected';

    public const AI_FAILED = 'failed';


    /*
    |--------------------------------------------------------------------------
    | Process Status
    |--------------------------------------------------------------------------
    */

    public const PROCESS_WAITING = 'waiting';

    public const PROCESS_RUNNING = 'processing';

    public const PROCESS_COMPLETED = 'completed';

    public const PROCESS_FAILED = 'failed';


    /*
    |--------------------------------------------------------------------------
    | Seller Review
    |--------------------------------------------------------------------------
    */

    public const SELLER_REVIEW_PENDING = 'pending';

    public const SELLER_REVIEW_APPROVED = 'approved';


    /*
    |--------------------------------------------------------------------------
    | Admin Review
    |--------------------------------------------------------------------------
    */

    public const REVIEW_PENDING = 'pending';

    public const REVIEW_APPROVED = 'approved';

    public const REVIEW_REJECTED = 'rejected';


    /*
    |--------------------------------------------------------------------------
    | Preview
    |--------------------------------------------------------------------------
    */

    public const PREVIEW_WAITING = 'waiting';

    public const PREVIEW_GENERATING = 'generating';

    public const PREVIEW_COMPLETED = 'completed';

    public const PREVIEW_FAILED = 'failed';


    /*
    |--------------------------------------------------------------------------
    | YouTube
    |--------------------------------------------------------------------------
    */

    public const YOUTUBE_NONE = 'none';

    public const YOUTUBE_UPLOADING = 'uploading';

    public const YOUTUBE_PRIVATE = 'private';

    public const YOUTUBE_PUBLIC = 'public';

    public const YOUTUBE_FAILED = 'failed';


    /*
    |--------------------------------------------------------------------------
    | Workflow Stage
    |--------------------------------------------------------------------------
    |
    | upload
    |   ↓
    | ai
    |   ↓
    | process
    |   ↓
    | preview
    |   ↓
    | seller_review
    |   ↓
    | review
    |   ↓
    | youtube
    |   ↓
    | completed
    |
    */

    public const STAGE_UPLOAD = 'upload';

    public const STAGE_AI = 'ai';

    public const STAGE_PROCESS = 'process';

    public const STAGE_PREVIEW = 'preview';

    public const STAGE_SELLER_REVIEW = 'seller_review';

    public const STAGE_REVIEW = 'review';

    public const STAGE_YOUTUBE = 'youtube';

    public const STAGE_COMPLETED = 'completed';


    /*
    |--------------------------------------------------------------------------
    | Edit Mode
    |--------------------------------------------------------------------------
    */

    public const EDIT_MODE_NONE = 'none';

    public const EDIT_MODE_PENDING = 'pending';

    public const EDIT_MODE_V1 = 'v1';

    public const EDIT_MODE_V2 = 'v2';

    public const EDIT_MODE_V3 = 'v3';


    /*
    |--------------------------------------------------------------------------
    | Product
    |--------------------------------------------------------------------------
    */

    public function product(): BelongsTo
    {
        return $this->belongsTo(
            Product::class,
            'product_id'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Shop
    |--------------------------------------------------------------------------
    */

    public function shop(): BelongsTo
    {
        return $this->belongsTo(
            Shop::class,
            'shop_id'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | 作成者等を将来追加する場合
    |--------------------------------------------------------------------------
    |
    | 現在の migration には created_by / updated_by が存在しないため、
    | ShopVideoDraft と違って creator / updater は定義しない。
    |
    */


    /*
    |--------------------------------------------------------------------------
    | Seller Reviewer
    |--------------------------------------------------------------------------
    */

    public function sellerReviewer(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'seller_reviewed_by'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Admin Reviewer
    |--------------------------------------------------------------------------
    */

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'reviewed_by'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | BGM
    |--------------------------------------------------------------------------
    */

    public function bgm(): BelongsTo
    {
        return $this->belongsTo(
            ShopVideoBgm::class,
            'bgm_id'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Scope
    |--------------------------------------------------------------------------
    */

    public function scopeAiCompleted($query)
    {
        return $query->where(
            'ai_status',
            self::AI_APPROVED
        );
    }


    public function scopeSellerApproved($query)
    {
        return $query->where(
            'seller_review_status',
            self::SELLER_REVIEW_APPROVED
        );
    }


    public function scopeReviewApproved($query)
    {
        return $query->where(
            'review_status',
            self::REVIEW_APPROVED
        );
    }


    public function scopeYoutubeCompleted($query)
    {
        return $query->whereIn(
            'youtube_status',
            [
                self::YOUTUBE_PRIVATE,
                self::YOUTUBE_PUBLIC,
            ]
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Workflow 判定
    |--------------------------------------------------------------------------
    */

    public function canStartYoutube(): bool
    {
        return
            $this->ai_status === self::AI_APPROVED
            &&
            $this->process_status === self::PROCESS_COMPLETED
            &&
            $this->preview_status === self::PREVIEW_COMPLETED
            &&
            $this->seller_review_status === self::SELLER_REVIEW_APPROVED
            &&
            $this->review_status === self::REVIEW_APPROVED
            &&
            !empty($this->processed_movie);
    }


    public function isYoutubeCompleted(): bool
    {
        return in_array(
            $this->youtube_status,
            [
                self::YOUTUBE_PRIVATE,
                self::YOUTUBE_PUBLIC,
            ],
            true
        );
    }


    public function isWorkflowCompleted(): bool
    {
        return
            $this->review_status === self::REVIEW_APPROVED
            &&
            $this->isYoutubeCompleted();
    }

    public function socialPosts(): HasMany
    {
        return $this->hasMany(
            ProductVideoSocialPost::class,
            'product_video_draft_id'
        );
    }



}

