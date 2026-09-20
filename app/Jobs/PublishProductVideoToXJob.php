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

class PublishProductVideoToXJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 3;

    protected int $videoId;

    public function __construct(int $videoId)
    {
        $this->videoId = $videoId;
    }

    public function handle(): void
    {
        $video = ProductVideoDraft::findOrFail(
            $this->videoId
        );

        $post = ProductVideoSocialPost::where(
            'product_video_draft_id',
            $video->id
        )
            ->where(
                'platform',
                ProductVideoSocialPost::PLATFORM_X
            )
            ->first();

        if (!$post) {
            return;
        }

        if (
            $post->status
                === ProductVideoSocialPost::STATUS_COMPLETED
        ) {
            return;
        }

        $post->update([
            'status'
                => ProductVideoSocialPost::STATUS_PROCESSING,
            'error_message'
                => null,
        ]);

        /*
        | X API
        |
        | 現時点では実際のX API接続は行わない。
        | X API認証・動画アップロード・投稿処理をここへ追加する。
        */

        return;
    }

    public function failed(Throwable $exception): void
    {
        $post = ProductVideoSocialPost::where(
            'product_video_draft_id',
            $this->videoId
        )
            ->where(
                'platform',
                ProductVideoSocialPost::PLATFORM_X
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

    public function middleware(): array
    {
        return [
            (new WithoutOverlapping(
                'product-video-x-' . $this->videoId
            ))
                ->releaseAfter(10)
                ->expireAfter(3600),
        ];
    }
}