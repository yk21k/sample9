<?php

namespace App\Jobs;

use App\Models\ProductVideoDraft;
use App\Models\ProductVideoSocialPost;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Queue\SerializesModels;
use Throwable;

class PublishProductVideoToTikTokJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Job最大試行回数
     */
    public int $tries = 3;

    /**
     * ProductVideoDraft ID
     */
    protected int $videoId;

    public function __construct(
        int $videoId
    ) {
        $this->videoId = $videoId;
    }

    /**
     * Job実行
     */
    public function handle(): void
    {
        $video = ProductVideoDraft::findOrFail(
            $this->videoId
        );

        /*
        |--------------------------------------------------------------------------
        | TikTok配信レコード取得
        |--------------------------------------------------------------------------
        */

        $post = ProductVideoSocialPost::where(
            'product_video_draft_id',
            $video->id
        )
            ->where(
                'platform',
                ProductVideoSocialPost::PLATFORM_TIKTOK
            )
            ->first();

        if (!$post) {
            return;
        }

        /*
        |--------------------------------------------------------------------------
        | すでに完了している場合
        |--------------------------------------------------------------------------
        */

        if (
            $post->status
                === ProductVideoSocialPost::STATUS_COMPLETED
        ) {
            return;
        }

        /*
        |--------------------------------------------------------------------------
        | 配信処理開始
        |--------------------------------------------------------------------------
        */

        $post->update([
            'status'
                => ProductVideoSocialPost::STATUS_PROCESSING,

            'error_message'
                => null,
        ]);

        /*
        |--------------------------------------------------------------------------
        | TikTok API
        |--------------------------------------------------------------------------
        |
        | 現時点では実際のTikTok API接続は行わない。
        | TikTok API認証・動画投稿処理をここへ追加する。
        |
        */

        return;
    }

    /**
     * Job最終失敗時
     */
    public function failed(
        Throwable $exception
    ): void {

        $post = ProductVideoSocialPost::where(
            'product_video_draft_id',
            $this->videoId
        )
            ->where(
                'platform',
                ProductVideoSocialPost::PLATFORM_TIKTOK
            )
            ->first();

        if (!$post) {
            return;
        }

        $post->update([
            'status'
                => ProductVideoSocialPost::STATUS_FAILED,

            'error_message'
                => $exception->getMessage(),
        ]);
    }

    /**
     * 同じ動画のTikTok配信重複実行を防止
     */
    public function middleware(): array
    {
        return [
            (new WithoutOverlapping(
                'product-video-tiktok-' . $this->videoId
            ))
                ->releaseAfter(10)
                ->expireAfter(3600),
        ];
    }
}