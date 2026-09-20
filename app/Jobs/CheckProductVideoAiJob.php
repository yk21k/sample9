<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

use App\Models\ProductVideoDraft;
use App\Services\Video\Ai\ProductVideoAiService;
use App\Services\Video\Workflow\ProductVideoWorkflowService;

class CheckProductVideoAiJob implements ShouldQueue
{
    use Dispatchable,
        InteractsWithQueue,
        Queueable,
        SerializesModels;

    protected int $videoId;

    public function __construct(
        int $videoId
    ) {
        $this->videoId = $videoId;
    }

    /**
     * Execute the job.
     */
    public function handle(
        ProductVideoWorkflowService $workflow,
        ProductVideoAiService $videoAi
    ): void {

        $video = ProductVideoDraft::findOrFail(
            $this->videoId
        );

        /*
        |--------------------------------------------------------------------------
        | AI結果取得
        |--------------------------------------------------------------------------
        */

        $result = $videoAi->getResult(
            $video
        );

        /*
        |--------------------------------------------------------------------------
        | AI処理中
        |--------------------------------------------------------------------------
        */

        if (
            $result['status']
                === ProductVideoDraft::AI_PROCESSING
        ) {

            self::dispatch(
                $video->id
            )->delay(
                now()->addSeconds(10)
            );

            return;
        }

        /*
        |--------------------------------------------------------------------------
        | AI失敗
        |--------------------------------------------------------------------------
        */

        if (
            $result['status']
                === ProductVideoDraft::AI_FAILED
        ) {

            $workflow->failAi(
                $video,
                $result['reason'] ?? null
            );

            return;
        }

        /*
        |--------------------------------------------------------------------------
        | AI完了
        |--------------------------------------------------------------------------
        */

        $workflow->completeAi(
            $video,
            $result
        );
    }
}