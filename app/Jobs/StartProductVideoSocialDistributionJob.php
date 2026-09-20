<?php

namespace App\Jobs;

use App\Models\ProductVideoDraft;
use App\Services\Video\Workflow\ProductVideoWorkflowService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Queue\SerializesModels;
use Throwable;
use App\Jobs\PublishProductVideoToInstagramJob;
use App\Jobs\PublishProductVideoToTikTokJob;
use App\Jobs\PublishProductVideoToFacebookJob;
use App\Jobs\PublishProductVideoToXJob;

class StartProductVideoSocialDistributionJob implements ShouldQueue
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
        ProductVideoWorkflowService $workflow
    ): void {

        $video = ProductVideoDraft::findOrFail(
            $this->videoId
        );

        /*
        |--------------------------------------------------------------------------
        | 管理者審査確認
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
        | YouTube完了確認
        |--------------------------------------------------------------------------
        */

        if (
            !in_array(
                $video->youtube_status,
                [
                    ProductVideoDraft::YOUTUBE_PRIVATE,
                    ProductVideoDraft::YOUTUBE_PUBLIC,
                ],
                true
            )
            ||
            empty($video->youtube_video_id)
        ) {
            return;
        }

        /*
        |--------------------------------------------------------------------------
        | SNS配信開始
        |--------------------------------------------------------------------------
        */

        $workflow->startSocialDistribution(
            $video
        );

        PublishProductVideoToInstagramJob::dispatch(
            $video->id
        );

        PublishProductVideoToTikTokJob::dispatch(
            $video->id
        );

        PublishProductVideoToFacebookJob::dispatch(
            $video->id
        );

        PublishProductVideoToXJob::dispatch(
            $video->id
        );
    }

    /**
     * Job最終失敗時
     */
    public function failed(
        Throwable $exception
    ): void {
        // 現時点ではSNS配信開始処理の失敗記録は
        // 各SNS配信Job側で管理する。
    }

    /**
     * 同じ動画のSNS配信開始重複実行を防止
     */
    public function middleware(): array
    {
        return [
            (new WithoutOverlapping(
                'product-video-social-start-' . $this->videoId
            ))
                ->releaseAfter(10)
                ->expireAfter(3600),
        ];
    }
}