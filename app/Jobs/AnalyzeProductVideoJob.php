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

class AnalyzeProductVideoJob implements ShouldQueue
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
        | AI開始
        |--------------------------------------------------------------------------
        */

        $workflow->startAi(
            $video,
            $videoAi
        );
    }
}