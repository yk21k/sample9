<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use Illuminate\Database\Eloquent\Model;

class ShopVideoDraft extends Model
{
    protected $fillable = [

        /*
        |--------------------------------------------------------------------------
        | 基本
        |--------------------------------------------------------------------------
        */

        'shop_id',

        'title',

        'description',

        /*
        |--------------------------------------------------------------------------
        | 作成情報
        |--------------------------------------------------------------------------
        */

        'created_by',

        'updated_by',

        /*
        |--------------------------------------------------------------------------
        | 元動画
        |--------------------------------------------------------------------------
        */

        'original_movie',

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
        | 加工
        |--------------------------------------------------------------------------
        */

        'process_status',

        'review_status',

        'reviewed_at',

        'reviewed_by',

        'processed_movie',

        'processed_at',

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
        | SNS
        |--------------------------------------------------------------------------
        */

        'youtube_status',

        'youtube_video_id',

        'youtube_uploaded_at',

        'instagram_status',

        'instagram_media_id',

        'instagram_uploaded_at',

        'tiktok_status',

        'tiktok_video_id',

        'tiktok_uploaded_at',

        'facebook_status',

        'facebook_video_id',

        'facebook_uploaded_at',

        'x_status',

        'x_media_id',

        'x_uploaded_at',

        /*
        |--------------------------------------------------------------------------
        | Shop
        |--------------------------------------------------------------------------
        */

        'publish_status',

        'published_at',

        /*
        |--------------------------------------------------------------------------
        | Workflow
        |--------------------------------------------------------------------------
        */

        'workflow_stage',

        'edit_mode',
        'bgm_id',

        /*
        |--------------------------------------------------------------------------
        | 出品者確認
        |--------------------------------------------------------------------------
        */

        'seller_review_status',

        'seller_reviewed_at',

        'seller_reviewed_by',


    ];

    protected $casts = [

        'moderation_labels' => 'array',

        'ai_reviewed_at' => 'datetime',

        'processed_at' => 'datetime',

        'preview_generated_at' => 'datetime',

        'youtube_uploaded_at' => 'datetime',

        'instagram_uploaded_at' => 'datetime',

        'tiktok_uploaded_at' => 'datetime',

        'facebook_uploaded_at' => 'datetime',

        'x_uploaded_at' => 'datetime',

        'published_at' => 'datetime',

        'seller_reviewed_at' => 'datetime',

    ];

    public const AI_PENDING = 'pending';

    public const AI_PROCESSING = 'processing';

    public const AI_APPROVED = 'approved';

    public const AI_WARNING = 'warning';

    public const AI_REJECTED = 'rejected';

    public const AI_FAILED = 'failed';

    public const REVIEW_PENDING = 'pending';

    public const REVIEW_APPROVED = 'approved';

    public const REVIEW_REJECTED = 'rejected';


    public const PROCESS_WAITING = 'waiting';

    public const PROCESS_RUNNING = 'processing';

    public const PROCESS_COMPLETED = 'completed';

    public const PROCESS_FAILED = 'failed';

    public const YOUTUBE_NONE = 'none';

    public const YOUTUBE_UPLOADING = 'uploading';

    public const YOUTUBE_PRIVATE = 'private';

    public const YOUTUBE_PUBLIC = 'public';

    public const YOUTUBE_FAILED = 'failed';

    public const STAGE_UPLOAD = 'upload';

    public const SNS_NONE = 'none';

    public const SNS_UPLOADING = 'uploading';

    public const SNS_PRIVATE = 'private';

    public const SNS_PUBLIC = 'public';

    public const SNS_FAILED = 'failed';

    public const STAGE_AI = 'ai';

    public const STAGE_SNS = 'sns';

    public const PUBLISH_DRAFT = 'draft';

    public const STAGE_PROCESS = 'process';

    public const STAGE_REVIEW = 'review';

    public const STAGE_PREVIEW = 'preview';

    public const STAGE_YOUTUBE = 'youtube';

    public const STAGE_INSTAGRAM = 'instagram';

    public const STAGE_PUBLISHED = 'published';

    public const STAGE_COMPLETED = 'completed';

    public const PREVIEW_WAITING='waiting';

    public const PREVIEW_GENERATING='generating';

    public const PREVIEW_COMPLETED='completed';

    public const PREVIEW_FAILED='failed';

    /*
    |--------------------------------------------------------------------------
    | Audit Actions
    |--------------------------------------------------------------------------
    */

    public const ACTION_UPLOAD_COMPLETED
        = 'shop_video_upload_completed';

    public const ACTION_AI_STARTED
        = 'shop_video_ai_started';

    public const ACTION_AI_COMPLETED
        = 'shop_video_ai_completed';

    public const ACTION_AI_FAILED
        = 'shop_video_ai_failed';   


    public const ACTION_PROCESS_STARTED
        = 'shop_video_process_started';    

    public const ACTION_PROCESS_COMPLETED
        = 'shop_video_process_completed';

    public const ACTION_PROCESS_FAILED 
        = 'shop_video_process_failed';
    
    public const ACTION_PREVIEW_STARTED
        = 'shop_video_preview_started';

    public const ACTION_PREVIEW_COMPLETED
        = 'shop_video_preview_completed';

    public const ACTION_PREVIEW_FAILED
        = 'shop_video_preview_failed';

    public const ACTION_REVIEW_APPROVED = 'shop_video_review_approved';
    
    public const ACTION_REVIEW_REJECTED = 'shop_video_review_rejected';    



    /*
    |--------------------------------------------------------------------------
    | Process Pipeline Version
    |--------------------------------------------------------------------------
    |
    | v1
    |   - ロゴ追加
    |
    | v2
    |   - ロゴ
    |   - BGM
    |   - エンディング
    |
    | v3
    |   - ロゴ
    |   - BGM
    |   - 字幕
    |   - AIナレーション
    |
    */

    public const EDIT_MODE_NONE = 'none';

    public const EDIT_MODE_PENDING = 'pending';

    public const EDIT_MODE_V1 = 'v1';

    public const EDIT_MODE_V2 = 'v2';

    public const EDIT_MODE_V3 = 'v3';

    public const SELLER_REVIEW_PENDING = 'pending';

    public const SELLER_REVIEW_APPROVED = 'approved';


    public const STAGE_SELLER_REVIEW = 'seller_review';

    public const ACTION_SELLER_REVIEW_APPROVED =
        'shop_video_seller_review_approved';


    public const PUBLISH_PUBLISHED = 'published';    


    public function scopeAiCompleted($query)
    {
        return $query->where(
            'ai_status',
            self::AI_APPROVED
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Shop
    |--------------------------------------------------------------------------
    */

    public function shop()
    {
        return $this->belongsTo(
            Shop::class
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

    /*
    |--------------------------------------------------------------------------
    | 更新者
    |--------------------------------------------------------------------------
    */

    public function updater()
    {
        return $this->belongsTo(
            User::class,
            'updated_by'
        );
    }

    public function review()
    {
        return $this->hasOne(
            ShopVideoReview::class
        );
    }

    /**
     * 使用するBGM
     */
    public function bgm(): BelongsTo
    {
        return $this->belongsTo(
            ShopVideoBgm::class,
            'bgm_id'
        );
    }


}
