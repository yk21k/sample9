<?php

namespace App\Services;

use Aws\Rekognition\RekognitionClient;
use Illuminate\Support\Facades\Log;

class RekognitionService
{
    protected $client;
    protected $bucket;

    public function __construct()
    {
        $this->client = new RekognitionClient([
            'region' => env('AWS_DEFAULT_REGION', 'ap-northeast-1'),
            'version' => 'latest',
            'credentials' => [
                'key' => env('AWS_ACCESS_KEY_ID'),
                'secret' => env('AWS_SECRET_ACCESS_KEY'),
            ],
        ]);

        // S3 バケット名（public バケット）
        $this->bucket = env('AWS_BUCKET');
    }

    /**
     * 画像または動画を解析
     * @param string $s3Key S3 上のキー（例: products/March2026/xxxxx.jpg）
     * @return array|null
     */
    public function analyze(string $s3Key)
    {
        if (!$s3Key) {
            Log::warning('No S3 key provided for Rekognition');
            return null;
        }

        try {
            // 画像として ModerationLabels を取得
            // $result = $this->client->detectModerationLabels([
            //     'Image' => [
            //         'S3Object' => [
            //             'Bucket' => $this->bucket,
            //             'Name' => $s3Key,
            //         ],
            //     ],
            //     'MinConfidence' => 50,
            // ]);

            $result = $this->client->detectModerationLabels([
                'Image' => [
                    'S3Object' => [
                        'Bucket' => env('AWS_BUCKET'),
                        'Name'   => $s3Key,
                    ],
                ],
            ]);

            Log::info('Rekognition Params', [
                'bucket' => env('AWS_BUCKET'),
                'key' => $s3Key
            ]);

            return $result['ModerationLabels'] ?? [];

        } catch (\Aws\Exception\AwsException $e) {
            Log::error("Rekognition error for {$s3Key}: " . $e->getMessage());
            return null;
        }
    }
}