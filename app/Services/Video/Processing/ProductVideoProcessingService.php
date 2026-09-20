<?php

namespace App\Services\Video\Processing;

use App\Models\ProductVideoDraft;
use App\Services\Video\Storage\ProductVideoStorageService;
use App\Services\Video\Processing\Versions\VideoEditV1;
use App\Services\Video\Processing\Versions\VideoEditV2;
use App\Services\Video\VideoMetadataService;
use RuntimeException;
use Illuminate\Support\Str;

class ProductVideoProcessingService
{
    public function __construct(
        protected ProductVideoStorageService $storage,
        protected FfmpegVideoProcessor $processor,
        protected VideoEditV1 $videoEditV1,
        protected VideoEditV2 $videoEditV2,
        protected VideoMetadataService $metadataService,
    ) {
    }

    /**
     * 商品SNS動画を加工する
     *
     * S3
     * ↓
     * ローカル一時ファイル
     * ↓
     * FFmpeg
     * ↓
     * S3
     */
    public function process(
        ProductVideoDraft $video
    ): array {

        $originalPath = $video->original_movie;

        if (
            !$originalPath ||
            !$this->storage->exists($originalPath)
        ) {
            throw new RuntimeException(
                '商品SNS動画の元動画がS3上に存在しません。'
            );
        }

        $inputPath = null;
        $outputPath = null;
        $bgmPath = null;

        $totalStart = microtime(true);

        logger()->info(
            'Product video processing START',
            [
                'video_id' => $video->id,
                'product_id' => $video->product_id,
                'original_path' => $originalPath,
                'edit_mode' => $video->edit_mode,
            ]
        );

        try {

            /*
            |--------------------------------------------------------------------------
            | S3 → ローカル
            |--------------------------------------------------------------------------
            */

            $start = microtime(true);

            logger()->info(
                'Product video processing: S3 download START',
                [
                    'video_id' => $video->id,
                    'original_path' => $originalPath,
                ]
            );

            $inputPath =
                $this->storage->downloadToLocal(
                    $originalPath
                );

            /*
            |--------------------------------------------------------------------------
            | メタデータ取得
            |--------------------------------------------------------------------------
            */

            $metadata =
                $this->metadataService->probe(
                    $inputPath
                );

            $videoWidth =
                (int) $metadata['width'];

            $videoHeight =
                (int) $metadata['height'];

            $videoDuration =
                (float) $metadata['duration'];

            $hasAudio =
                (bool) ($metadata['has_audio'] ?? false);

            logger()->info(
                'Product video processing: metadata DONE',
                [
                    'video_id' => $video->id,
                    'width' => $videoWidth,
                    'height' => $videoHeight,
                    'duration' => $videoDuration,
                    'has_audio' => $hasAudio,
                ]
            );

            logger()->info(
                'Product video processing: S3 download DONE',
                [
                    'video_id' => $video->id,
                    'input_path' => $inputPath,
                    'elapsed_seconds' => round(
                        microtime(true) - $start,
                        3
                    ),
                    'file_size' => file_exists($inputPath)
                        ? filesize($inputPath)
                        : null,
                ]
            );

            /*
            |--------------------------------------------------------------------------
            | ローカル出力先
            |--------------------------------------------------------------------------
            */

            $outputPath =
                storage_path(
                    'app/video-temp/product_processed_'
                    . Str::uuid()
                    . '.mp4'
                );

            logger()->info(
                'Product video processing: output path created',
                [
                    'video_id' => $video->id,
                    'output_path' => $outputPath,
                ]
            );

            /*
            |--------------------------------------------------------------------------
            | 編集モード
            |--------------------------------------------------------------------------
            */

            $options = [];

            switch ($video->edit_mode) {

                /*
                |--------------------------------------------------------------------------
                | V1
                |--------------------------------------------------------------------------
                */

                case ProductVideoDraft::EDIT_MODE_V1:

                    $logoPath = storage_path(
                        'app/video/logo.png'
                    );

                    logger()->info(
                        'Product video processing: VideoEditV1 START',
                        [
                            'video_id' => $video->id,
                            'logo_path' => $logoPath,
                        ]
                    );

                    $options =
                        $this->videoEditV1->build(
                            $logoPath
                        );

                    logger()->info(
                        'Product video processing: VideoEditV1 DONE',
                        [
                            'video_id' => $video->id,
                            'options' => $options,
                        ]
                    );

                    break;


                /*
                |--------------------------------------------------------------------------
                | V2
                |--------------------------------------------------------------------------
                */

                case ProductVideoDraft::EDIT_MODE_V2:

                    $logoPath = storage_path(
                        'app/video/logo.png'
                    );

                    $endingPath = storage_path(
                        'app/video/ending.mp4'
                    );

                    /*
                    |--------------------------------------------------------------------------
                    | BGM確認
                    |--------------------------------------------------------------------------
                    */

                    if (!$video->bgm_id) {
                        throw new RuntimeException(
                            'V2ではBGMが選択されていません。'
                        );
                    }

                    $bgm = $video->bgm;

                    if (!$bgm) {
                        throw new RuntimeException(
                            '指定されたBGMが存在しません。'
                        );
                    }

                    if (!$bgm->file_path) {
                        throw new RuntimeException(
                            '指定されたBGMのファイルパスが設定されていません。'
                        );
                    }

                    logger()->info(
                        'Product video processing: BGM download START',
                        [
                            'video_id' => $video->id,
                            'bgm_id' => $bgm->id,
                            'bgm_path' => $bgm->file_path,
                        ]
                    );

                    /*
                    |--------------------------------------------------------------------------
                    | S3 → ローカル BGM
                    |--------------------------------------------------------------------------
                    */

                    $bgmPath =
                        $this->storage->downloadToLocal(
                            $bgm->file_path
                        );

                    logger()->info(
                        'Product video processing: BGM download DONE',
                        [
                            'video_id' => $video->id,
                            'bgm_id' => $bgm->id,
                            'local_path' => $bgmPath,
                        ]
                    );

                    /*
                    |--------------------------------------------------------------------------
                    | V2 FFmpeg設定
                    |--------------------------------------------------------------------------
                    */

                    logger()->info(
                        'Product video processing: VideoEditV2 START',
                        [
                            'video_id' => $video->id,
                            'logo_path' => $logoPath,
                            'bgm_path' => $bgmPath,
                            'ending_path' => $endingPath,
                            'duration' => $videoDuration,
                            'width' => $videoWidth,
                            'height' => $videoHeight,
                            'has_audio' => $hasAudio,
                        ]
                    );

                    $options =
                        $this->videoEditV2->build(
                            $logoPath,
                            $bgmPath,
                            $endingPath,
                            $videoDuration,
                            $videoWidth,
                            $videoHeight,
                            $hasAudio
                        );

                    logger()->info(
                        'Product video processing: VideoEditV2 DONE',
                        [
                            'video_id' => $video->id,
                            'options' => $options,
                        ]
                    );

                    break;


                /*
                |--------------------------------------------------------------------------
                | 未対応
                |--------------------------------------------------------------------------
                */

                default:

                    throw new RuntimeException(
                        '未対応の商品SNS動画加工バージョンです: '
                        . $video->edit_mode
                    );
            }

            /*
            |--------------------------------------------------------------------------
            | FFmpeg
            |--------------------------------------------------------------------------
            */

            $start = microtime(true);

            logger()->info(
                'Product video processing: FFmpeg START',
                [
                    'video_id' => $video->id,
                    'input_path' => $inputPath,
                    'output_path' => $outputPath,
                    'options' => $options,
                ]
            );

            $result =
                $this->processor->process(
                    $inputPath,
                    $outputPath,
                    $options
                );

            logger()->info(
                'Product video processing: FFmpeg DONE',
                [
                    'video_id' => $video->id,
                    'elapsed_seconds' => round(
                        microtime(true) - $start,
                        3
                    ),
                    'output_exists' => file_exists($outputPath),
                    'output_size' => file_exists($outputPath)
                        ? filesize($outputPath)
                        : null,
                    'duration' => $result['duration'] ?? null,
                ]
            );

            /*
            |--------------------------------------------------------------------------
            | S3保存先
            |--------------------------------------------------------------------------
            */

            $processedPath =
                $this->storage->generateProcessedPath();

            logger()->info(
                'Product video processing: processed path generated',
                [
                    'video_id' => $video->id,
                    'processed_path' => $processedPath,
                ]
            );

            /*
            |--------------------------------------------------------------------------
            | ローカル → S3
            |--------------------------------------------------------------------------
            */

            $start = microtime(true);

            logger()->info(
                'Product video processing: S3 upload START',
                [
                    'video_id' => $video->id,
                    'local_path' => $outputPath,
                    's3_path' => $processedPath,
                    'file_size' => file_exists($outputPath)
                        ? filesize($outputPath)
                        : null,
                ]
            );

            $this->storage->uploadFromLocal(
                $outputPath,
                $processedPath
            );

            logger()->info(
                'Product video processing: S3 upload DONE',
                [
                    'video_id' => $video->id,
                    's3_path' => $processedPath,
                    'elapsed_seconds' => round(
                        microtime(true) - $start,
                        3
                    ),
                ]
            );

            /*
            |--------------------------------------------------------------------------
            | S3ファイルサイズ
            |--------------------------------------------------------------------------
            */

            $fileSize =
                $this->storage->size(
                    $processedPath
                );

            /*
            |--------------------------------------------------------------------------
            | 完了
            |--------------------------------------------------------------------------
            */

            logger()->info(
                'Product video processing DONE',
                [
                    'video_id' => $video->id,
                    'product_id' => $video->product_id,
                    'total_elapsed_seconds' => round(
                        microtime(true) - $totalStart,
                        3
                    ),
                    'processed_path' => $processedPath,
                    'duration' => $result['duration']
                        ?? $videoDuration,
                    'file_size' => $fileSize,
                ]
            );

            return [

                'processed_movie'
                    => $processedPath,

                'duration'
                    => $result['duration']
                        ?? $videoDuration,

                'file_size'
                    => $fileSize,

            ];

        } finally {

            /*
            |--------------------------------------------------------------------------
            | 一時ファイル削除
            |--------------------------------------------------------------------------
            */

            logger()->info(
                'Product video processing: temp cleanup START',
                [
                    'video_id' => $video->id,
                    'input_path' => $inputPath,
                    'output_path' => $outputPath,
                    'bgm_path' => $bgmPath,
                ]
            );

            $this->storage->deleteTempFile(
                $inputPath
            );

            $this->storage->deleteTempFile(
                $outputPath
            );

            $this->storage->deleteTempFile(
                $bgmPath
            );

            logger()->info(
                'Product video processing: temp cleanup DONE',
                [
                    'video_id' => $video->id,
                ]
            );
        }
    }
}