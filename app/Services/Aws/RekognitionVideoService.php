<?php

namespace App\Services\Aws;

use Aws\Rekognition\RekognitionClient;

class RekognitionVideoService
{
    public function __construct(
        protected RekognitionClient $client
    ) {
    }

    public function startContentModeration(
        string $bucket,
        string $key
    ): string {

        $result = $this->client
            ->startContentModeration([

                'Video' => [

                    'S3Object' => [

                        'Bucket' => $bucket,

                        'Name' => $key,

                    ],

                ],

            ]);

        if (empty($result['JobId'])) {

            throw new \RuntimeException(
                'Amazon RekognitionのJobIdを取得できませんでした。'
            );
        }

        return $result['JobId'];
    }

    public function getContentModeration(
        string $jobId
    ): array {

        $allLabels = [];

        $nextToken = null;

        $firstResult = null;

        do {

            $params = [

                'JobId'
                    => $jobId,

                /*
                |--------------------------------------------------------------------------
                | 1回の取得件数
                |--------------------------------------------------------------------------
                */

                'MaxResults'
                    => 1000,

            ];

            if ($nextToken) {

                $params['NextToken']
                    = $nextToken;
            }

            $result = $this->client
                ->getContentModeration(
                    $params
                )
                ->toArray();

            /*
            |--------------------------------------------------------------------------
            | 最初のレスポンスを保持
            |--------------------------------------------------------------------------
            |
            | JobStatus / VideoMetadata /
            | ModerationModelVersion 等は
            | 最初のレスポンスを基準にする。
            |
            */

            if ($firstResult === null) {

                $firstResult = $result;

            }

            /*
            |--------------------------------------------------------------------------
            | ModerationLabelsを追加
            |--------------------------------------------------------------------------
            */

            foreach (
                $result['ModerationLabels'] ?? []
                as $label
            ) {

                $allLabels[] = $label;

            }

            /*
            |--------------------------------------------------------------------------
            | 次ページ
            |--------------------------------------------------------------------------
            */

            $nextToken =
                $result['NextToken'] ?? null;

        } while ($nextToken);

        /*
        |--------------------------------------------------------------------------
        | 最終結果
        |--------------------------------------------------------------------------
        */

        $firstResult['ModerationLabels']
            = $allLabels;

        unset(
            $firstResult['NextToken']
        );

        return $firstResult;
    }

    
}