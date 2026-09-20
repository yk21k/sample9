<?php

namespace App\Services\Video\Ai;

use App\Models\ProductVideoDraft;
use App\Services\Aws\RekognitionVideoService;

class ProductVideoAiService
{
    public function __construct(
        protected RekognitionVideoService $rekognition
    ) {
    }

    /**
     * AI解析開始
     *
     * Rekognition StartContentModeration
     */
    public function start(
        ProductVideoDraft $video
    ): array {

        $jobId = $this->rekognition
            ->startContentModeration(
                config('filesystems.disks.s3.bucket'),
                $video->original_movie
            );

        return [

            'status'
                => ProductVideoDraft::AI_PROCESSING,

            'job_id'
                => $jobId,

            'provider'
                => 'amazon_rekognition',

        ];
    }

    /**
     * AI結果取得
     *
     * Rekognition GetContentModeration
     */
    public function getResult(
        ProductVideoDraft $video
    ): array {

        $result = $this->rekognition
            ->getContentModeration(
                $video->rekognition_job_id
            );

        $jobStatus = $result['JobStatus'] ?? null;

        /*
        |--------------------------------------------------------------------------
        | AI処理中
        |--------------------------------------------------------------------------
        */

        if (
            $jobStatus === 'IN_PROGRESS'
        ) {

            return [

                'status'
                    => ProductVideoDraft::AI_PROCESSING,

                'provider'
                    => 'amazon_rekognition',

            ];
        }

        /*
        |--------------------------------------------------------------------------
        | AI処理失敗
        |--------------------------------------------------------------------------
        */

        if (
            $jobStatus === 'FAILED'
        ) {

            return [

                'status'
                    => ProductVideoDraft::AI_FAILED,

                'provider'
                    => 'amazon_rekognition',

                'reason'
                    => $result['StatusMessage']
                        ?? 'Amazon Rekognitionの動画解析に失敗しました。',

            ];
        }

        /*
        |--------------------------------------------------------------------------
        | AI解析完了
        |--------------------------------------------------------------------------
        */

        if (
            $jobStatus === 'SUCCEEDED'
        ) {

            return app(
                ModerationAnalyzer::class
            )->analyze(
                $result
            );
        }

        /*
        |--------------------------------------------------------------------------
        | 想定外の状態
        |--------------------------------------------------------------------------
        */

        return [

            'status'
                => ProductVideoDraft::AI_FAILED,

            'provider'
                => 'amazon_rekognition',

            'reason'
                => 'Amazon Rekognitionから想定外のステータスが返されました。'
                . ' status='
                . ($jobStatus ?? 'null'),

        ];
    }
}