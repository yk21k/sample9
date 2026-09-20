<?php

namespace App\Jobs;

use App\Models\ShopVideoDraft;
use App\Services\Video\Workflow\ShopVideoWorkflowService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

class GenerateShopVideoPreviewJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 3;

    public function __construct(
        public ShopVideoDraft $video
    ) {
    }

    public function handle(
        ShopVideoWorkflowService $workflow
    ): void {

        $workflow->startPreview(
            $this->video
        );
    }

    public function failed(
        Throwable $exception
    ): void {

        app(
            ShopVideoWorkflowService::class
        )->failPreview(
            $this->video,
            $exception->getMessage()
        );
    }
}

