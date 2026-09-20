<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

use App\Models\Shop;
use App\Models\ShopVideoDraft;
use App\Services\Video\Workflow\ShopVideoWorkflowService;
use App\Services\Video\Processing\VideoProcessingService;
use App\Services\Video\Usage\ShopVideoUsageService;
use Throwable;

class ProcessShopVideoJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Jobの最大実行時間
     */
    public $timeout = 300;

    /**
     * 自動リトライは行わない
     *
     * 動画加工利用回数を1回として扱うため、
     * 自動リトライによる二重消費を防止する。
     */
    public $tries = 1;

    protected int $videoId;

    public function __construct(
        int $videoId
    ) {
        $this->videoId = $videoId;
    }

    public function handle(
        ShopVideoWorkflowService $workflow,
        VideoProcessingService $processor,
        ShopVideoUsageService $usageService
    ): void {

        $video = ShopVideoDraft::findOrFail(
            $this->videoId
        );

        $shop = Shop::findOrFail(
            $video->shop_id
        );

        \Log::info(
            'ProcessShopVideoJob video loaded',
            [
                'video_id' => $video->id,
                'shop_id' => $video->shop_id,
                'edit_mode' => $video->edit_mode,
                'bgm_id' => $video->bgm_id,
            ]
        );

        $processingStarted = false;

        try {

            /*
            |--------------------------------------------------------------------------
            | 加工開始
            |--------------------------------------------------------------------------
            |
            | 利用回数消費
            | +
            | process_status = processing
            |
            */

            $usageService->startProcessing(
                $shop,
                $video
            );

            /*
            |--------------------------------------------------------------------------
            | ここまで到達したら加工開始権を取得済み
            |--------------------------------------------------------------------------
            */

            $processingStarted = true;

            $video->refresh();

            $workflow->startProcessing(
                $video
            );

            /*
            |--------------------------------------------------------------------------
            | 動画加工
            |--------------------------------------------------------------------------
            */

            $result = $processor->process(
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

        } catch (Throwable $e) {

            /*
            |--------------------------------------------------------------------------
            | 加工開始後の失敗だけPROCESS_FAILEDにする
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
     * Job最終失敗時
     */
    public function failed(
        Throwable $exception
    ): void {

        $video = ShopVideoDraft::find(
            $this->videoId
        );

        if (!$video) {
            return;
        }

        \Log::error(
            'ProcessShopVideoJob failed',
            [
                'video_id'
                    => $video->id,

                'shop_id'
                    => $video->shop_id,

                'message'
                    => $exception->getMessage(),
            ]
        );
    }
}