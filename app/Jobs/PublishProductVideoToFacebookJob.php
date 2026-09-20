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

class PublishProductVideoToFacebookJob implements ShouldQueue
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
                ProductVideoSocialPost::PLATFORM_FACEBOOK
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
        | Facebook API
        |
        | 現時点では実際のFacebook API接続は行わない。
        | Meta API認証・動画投稿処理をここへ追加する。
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
                ProductVideoSocialPost::PLATFORM_FACEBOOK
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
                'product-video-facebook-' . $this->videoId
            ))
                ->releaseAfter(10)
                ->expireAfter(3600),
        ];
    }
}