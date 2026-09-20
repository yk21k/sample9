<?php

namespace App\Jobs;

use App\Models\ProductVideoDraft;
use App\Services\Video\YouTube\YouTubeService;
use App\Services\Video\Workflow\ProductVideoWorkflowService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Queue\SerializesModels;
use Throwable;
use App\Jobs\StartProductVideoSocialDistributionJob;


class UploadProductVideoToYoutubeJob implements ShouldQueue
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
    public function handle(
        ProductVideoWorkflowService $workflow,
        YouTubeService $youtube
    ): void {

        $video = ProductVideoDraft::findOrFail(
            $this->videoId
        );

        /*
        |--------------------------------------------------------------------------
        | すでにYouTube登録済みなら何もしない
        |--------------------------------------------------------------------------
        */

        if (
            $video->youtube_status
                === ProductVideoDraft::YOUTUBE_PRIVATE
            && !empty($video->youtube_video_id)
        ) {
            return;
        }

        if (
            $video->youtube_status
                === ProductVideoDraft::YOUTUBE_PUBLIC
            && !empty($video->youtube_video_id)
        ) {
            return;
        }

        /*
        |--------------------------------------------------------------------------
        | 管理者審査承認確認
        |--------------------------------------------------------------------------
        */

        if (
            $video->review_status
                !== ProductVideoDraft::REVIEW_APPROVED
        ) {
            return;
        }

        /*
        |--------------------------------------------------------------------------
        | YouTubeアップロード開始
        |--------------------------------------------------------------------------
        */

        $workflow->startYoutubeUpload(
            $video
        );

        try {

            /*
            |--------------------------------------------------------------------------
            | YouTubeアップロード
            |--------------------------------------------------------------------------
            */

            $result = $youtube->upload(
                $video
            );

            /*
            |--------------------------------------------------------------------------
            | YouTubeアップロード完了
            |--------------------------------------------------------------------------
            */

            $workflow->completeYoutubeUpload(
                $video,
                $result
            );

            StartProductVideoSocialDistributionJob::dispatch(
                $video->id
            );

        } catch (Throwable $e) {

            /*
            |--------------------------------------------------------------------------
            | YouTubeアップロード失敗
            |--------------------------------------------------------------------------
            */

            $workflow->failYoutubeUpload(
                $video,
                $e->getMessage()
            );

            throw $e;
        }
    }

    /**
     * Job最終失敗時
     */
    public function failed(
        Throwable $exception
    ): void {

        $video = ProductVideoDraft::find(
            $this->videoId
        );

        if (!$video) {
            return;
        }

        app(
            ProductVideoWorkflowService::class
        )->failYoutubeUpload(
            $video,
            $exception->getMessage()
        );
    }

    /**
     * 同じ動画のYouTubeアップロード重複実行を防止
     */
    public function middleware(): array
    {
        return [
            (new WithoutOverlapping(
                'product-video-youtube-' . $this->videoId
            ))
                ->releaseAfter(10)
                ->expireAfter(3600),
        ];
    }
}