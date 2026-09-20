<?php

namespace App\Jobs;

use App\Models\ShopVideoDraft;
use App\Services\Video\YouTube\YouTubeService;
use App\Services\Video\Workflow\ShopVideoWorkflowService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;
use Illuminate\Queue\Middleware\WithoutOverlapping;

class UploadShopVideoToYoutubeJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 3;

    protected int $videoId;

    public function __construct(
        int $videoId
    ) {
        $this->videoId = $videoId;
    }

    public function handle(
        ShopVideoWorkflowService $workflow,
        YouTubeService $youtube
    ): void {

        $video = ShopVideoDraft::findOrFail(
            $this->videoId
        );

        /*
        |--------------------------------------------------------------------------
        | 既にYouTubeへアップロード済みなら終了
        |--------------------------------------------------------------------------
        */

        if (
            $video->youtube_status
                === ShopVideoDraft::YOUTUBE_PRIVATE
            && !empty($video->youtube_video_id)
        ) {

            logger()->info(
                'YouTube upload skipped: already uploaded',
                [
                    'shop_video_id' =>
                        $video->id,

                    'youtube_video_id' =>
                        $video->youtube_video_id,
                ]
            );

            return;
        }

        /*
        |--------------------------------------------------------------------------
        | Publicまで進んでいる場合も再アップロードしない
        |--------------------------------------------------------------------------
        */

        if (
            $video->youtube_status
                === ShopVideoDraft::YOUTUBE_PUBLIC
            && !empty($video->youtube_video_id)
        ) {

            logger()->info(
                'YouTube upload skipped: already published',
                [
                    'shop_video_id' =>
                        $video->id,

                    'youtube_video_id' =>
                        $video->youtube_video_id,
                ]
            );

            return;
        }

        /*
        |--------------------------------------------------------------------------
        | 承認状態確認
        |--------------------------------------------------------------------------
        */

        if (
            $video->review_status
                !== ShopVideoDraft::REVIEW_APPROVED
        ) {

            logger()->warning(
                'YouTube upload skipped: video is not approved',
                [
                    'shop_video_id' =>
                        $video->id,

                    'review_status' =>
                        $video->review_status,
                ]
            );

            return;
        }

        /*
        |--------------------------------------------------------------------------
        | YouTube Upload開始
        |--------------------------------------------------------------------------
        */

        $workflow->startYoutubeUpload(
            $video
        );

        try {

            $result = $youtube->upload(
                $video
            );

            $workflow->completeYoutubeUpload(
                $video,
                $result
            );

        } catch (Throwable $e) {

            $workflow->failYoutubeUpload(
                $video,
                $e->getMessage()
            );

            throw $e;
        }
    }

    public function failed(
        Throwable $exception
    ): void {

        $video = ShopVideoDraft::find(
            $this->videoId
        );

        if (!$video) {
            return;
        }

        app(
            ShopVideoWorkflowService::class
        )->failYoutubeUpload(
            $video,
            $exception->getMessage()
        );
    }

    /**
     * Queue Middleware
     */
    public function middleware(): array
    {
        return [
            (new WithoutOverlapping(
                'shop-video-youtube-' . $this->videoId
            ))
                ->releaseAfter(10)
                ->expireAfter(3600),
        ];
    }
}