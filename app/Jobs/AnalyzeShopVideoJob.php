<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

use App\Models\ShopVideoDraft;
use App\Services\Video\Workflow\ShopVideoWorkflowService;
use App\Services\Video\Ai\VideoAiService;


class AnalyzeShopVideoJob implements ShouldQueue
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
        ShopVideoWorkflowService $workflow,
        VideoAiService $videoAi
    ): void {

        $video = ShopVideoDraft::findOrFail(
            $this->videoId
        );


        /*
        |--------------------------------------------------------------------------
        | AI開始
        |--------------------------------------------------------------------------
        |
        | Amazon Rekognition StartContentModeration
        |
        */

        $workflow->startAi(
            $video,
            $videoAi
        );

    }
}