<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use App\Models\ProductVideoDraft;
use App\Services\Video\Workflow\ProductVideoWorkflowService;
use App\Services\Video\Processing\ProductVideoProcessingService;
use Throwable;

class ProcessProductVideoJob implements ShouldQueue
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
        ProductVideoProcessingService $processor
    ): void {

        $video = ProductVideoDraft::findOrFail(
            $this->videoId
        );

        \Log::info(
            'ProcessProductVideoJob video loaded',
            [
                'video_id' => $video->id,
                'product_id' => $video->product_id,
                'shop_id' => $video->shop_id,
                'edit_mode' => $video->edit_mode,
                'ai_status' => $video->ai_status,
                'process_status' => $video->process_status,
            ]
        );

        /*
        |--------------------------------------------------------------------------
        | 加工開始条件チェック
        |--------------------------------------------------------------------------
        */

        if (
            $video->ai_status
                !== ProductVideoDraft::AI_APPROVED
        ) {
            throw new \RuntimeException(
                'AI承認済みの商品SNS動画のみ加工できます。'
                . ' ai_status='
                . $video->ai_status
            );
        }

        if (
            $video->process_status
                !== ProductVideoDraft::PROCESS_WAITING
        ) {
            throw new \RuntimeException(
                '加工待ちの商品SNS動画のみ加工を開始できます。'
                . ' process_status='
                . $video->process_status
            );
        }

        /*
        |--------------------------------------------------------------------------
        | 編集モードチェック
        |--------------------------------------------------------------------------
        */

        if (
            !in_array(
                $video->edit_mode,
                [
                    ProductVideoDraft::EDIT_MODE_V1,
                    ProductVideoDraft::EDIT_MODE_V2,
                ],
                true
            )
        ) {
            throw new \RuntimeException(
                '未対応の商品SNS動画加工モードです: '
                . $video->edit_mode
            );
        }

        $processingStarted = false;

        try {

            /*
            |--------------------------------------------------------------------------
            | 加工開始
            |--------------------------------------------------------------------------
            */

            $workflow->startProcessing(
                $video
            );

            $processingStarted = true;

            /*
            |--------------------------------------------------------------------------
            | 動画加工
            |--------------------------------------------------------------------------
            */

            $result =
                $processor->process(
                    $video
                );

            /*
            |--------------------------------------------------------------------------
            | 加工完了
            |--------------------------------------------------------------------------
            */

            $workflow->completeProcessing(
                $video,
                $result
            );

            /*
            |--------------------------------------------------------------------------
            | Preview生成開始
            |--------------------------------------------------------------------------
            */

            GenerateProductVideoPreviewJob::dispatch(
                $video->id
            );

        } catch (Throwable $e) {

            /*
            |--------------------------------------------------------------------------
            | 加工失敗
            |--------------------------------------------------------------------------
            */

            if ($processingStarted) {

                $video->refresh();

                $workflow->failProcessing(
                    $video,
                    $e->getMessage()
                );
            }

            throw $e;
        }
    }

    /**
     * Jobの重複実行防止
     */
    public function middleware(): array
    {
        return [
            (new WithoutOverlapping(
                'product-video-processing-' . $this->videoId
            ))
                ->releaseAfter(10)
                ->expireAfter(3600),
        ];
    }


    /**
     * Job自体が失敗した場合
     */
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
            'ProcessProductVideoJob failed',
            [
                'video_id' => $video->id,
                'product_id' => $video->product_id,
                'shop_id' => $video->shop_id,
                'message' => $exception->getMessage(),
            ]
        );
    }
}