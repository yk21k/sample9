<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

use App\Models\ProductVideoDraft;
use App\Services\Video\Preview\ProductVideoPreviewService;
use App\Services\Video\Workflow\ProductVideoWorkflowService;
use Throwable;

class GenerateProductVideoPreviewJob implements ShouldQueue
{
    use Dispatchable,
        InteractsWithQueue,
        Queueable,
        SerializesModels;

    public $timeout = 300;

    public $tries = 1;

    protected int $videoId;

    public function __construct(
        int $videoId
    ) {
        $this->videoId = $videoId;
    }

    public function handle(
        ProductVideoWorkflowService $workflow,
        ProductVideoPreviewService $previewService
    ): void {

        $video = ProductVideoDraft::findOrFail(
            $this->videoId
        );

        \Log::info(
            'GenerateProductVideoPreviewJob video loaded',
            [
                'video_id' => $video->id,
                'product_id' => $video->product_id,
                'shop_id' => $video->shop_id,
                'process_status' => $video->process_status,
                'processed_movie' => $video->processed_movie,
                'preview_status' => $video->preview_status,
                'workflow_stage' => $video->workflow_stage,
            ]
        );

        if (
            $video->process_status
                !== ProductVideoDraft::PROCESS_COMPLETED
        ) {
            throw new \RuntimeException(
                '加工完了済みの商品SNS動画のみPreviewを生成できます。'
                . ' process_status='
                . $video->process_status
            );
        }

        if (!$video->processed_movie) {
            throw new \RuntimeException(
                'Preview生成対象の加工済み動画がありません。'
            );
        }

        if (
            $video->preview_status
                !== ProductVideoDraft::PREVIEW_WAITING
        ) {
            throw new \RuntimeException(
                'Preview待ちの商品SNS動画のみPreviewを生成できます。'
                . ' preview_status='
                . $video->preview_status
            );
        }

        $previewStarted = false;

        try {

            $workflow->startPreview(
                $video
            );

            $previewStarted = true;

            $result =
                $previewService->generate(
                    $video
                );

            $workflow->completePreview(
                $video,
                $result
            );

        } catch (Throwable $e) {

            if ($previewStarted) {

                $video->refresh();

                $workflow->failPreview(
                    $video,
                    $e->getMessage()
                );
            }

            throw $e;
        }
    }

    public function failed(
        Throwable $exception
    ): void {

        $video =
            ProductVideoDraft::find(
                $this->videoId
            );

        if (!$video) {
            return;
        }

        \Log::error(
            'GenerateProductVideoPreviewJob failed',
            [
                'video_id' => $video->id,
                'product_id' => $video->product_id,
                'shop_id' => $video->shop_id,
                'message' => $exception->getMessage(),
            ]
        );
    }
}