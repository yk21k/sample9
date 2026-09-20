<?php

namespace App\Services\Video\Processing;

use App\Models\ShopVideoDraft;
use App\Services\Video\Storage\VideoStorageService;
use RuntimeException;
use App\Services\Video\Processing\Versions\VideoEditV1;
use App\Services\Video\Processing\Versions\VideoEditV2;
use App\Services\Video\Processing\Versions\VideoEditV3;
use App\Services\Video\VideoMetadataService;

class VideoProcessingService
{
    public function __construct(
        VideoStorageService $storage,
        FfmpegVideoProcessor $processor,
        VideoEditV1 $videoEditV1,
        VideoEditV2 $videoEditV2,
        VideoEditV3 $videoEditV3,
        NarrationProcessor $narrationProcessor,
        VideoMetadataService $metadataService,
    ) {
        $this->storage = $storage;
        $this->processor = $processor;
        $this->videoEditV1 = $videoEditV1;
        $this->videoEditV2 = $videoEditV2;
        $this->videoEditV3 = $videoEditV3;
        $this->narrationProcessor = $narrationProcessor;
        $this->metadataService = $metadataService;
    }

    /**
     * 動画加工
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
        ShopVideoDraft $video
    ): array {

        $originalPath = $video->original_movie;

        if (
            !$originalPath ||
            !$this->storage->exists($originalPath)
        ) {
            throw new RuntimeException(
                '元動画がS3上に存在しません。'
            );
        }

        $inputPath = null;
        $outputPath = null;
        $bgmPath = null;
        $narrationPath = null;

        /*
        |--------------------------------------------------------------------------
        | 全体処理開始
        |--------------------------------------------------------------------------
        */

        $totalStart = microtime(true);

        logger()->info(
            'Video processing START',
            [
                'video_id' => $video->id,
                'original_path' => $originalPath,
                'edit_mode' => $video->edit_mode,
            ]
        );

        try {

            /*
            |--------------------------------------------------------------------------
            | S3 → ローカル一時ファイル
            |--------------------------------------------------------------------------
            */

            $start = microtime(true);

            logger()->info(
                'Video processing: S3 download START',
                [
                    'video_id' => $video->id,
                    'original_path' => $originalPath,
                ]
            );

            $inputPath =
                $this->storage->downloadToLocal(
                    $originalPath
                );

            $metadata = $this->metadataService->probe(
                $inputPath
            );

            $videoWidth = (int) $metadata['width'];
            $videoHeight = (int) $metadata['height'];
            $videoDuration = (float) $metadata['duration'];
            
            logger()->info(
                'Video processing: metadata DONE',
                [
                    'video_id' => $video->id,
                    'width' => $videoWidth,
                    'height' => $videoHeight,
                    'duration' => $videoDuration,
                ]
            );    

            logger()->info(
                'Video processing: S3 download DONE',
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
                    'app/video-temp/processed_' .
                    \Illuminate\Support\Str::uuid() .
                    '.mp4'
                );

            logger()->info(
                'Video processing: output path created',
                [
                    'video_id' => $video->id,
                    'output_path' => $outputPath,
                ]
            );

            /*
            |--------------------------------------------------------------------------
            | 編集バージョン判定
            |--------------------------------------------------------------------------
            */

            $options = [];

            switch ($video->edit_mode) {

                case 'v1':

                    $logoPath = storage_path(
                        'app/video/logo.png'
                    );

                    logger()->info(
                        'Video processing: VideoEditV1 START',
                        [
                            'video_id' => $video->id,
                            'logo_path' => $logoPath,
                        ]
                    );

                    $options = $this->videoEditV1->build(
                        $logoPath
                    );

                    logger()->info(
                        'Video processing: VideoEditV1 DONE',
                        [
                            'video_id' => $video->id,
                            'options' => $options,
                        ]
                    );

                break;

                case 'v2':

                    logger()->info(
                        'Video processing: VideoEditV2 START',
                        [
                            'video_id' => $video->id,
                        ]
                    );

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

                    /*
                    |--------------------------------------------------------------------------
                    | BGM S3 → Local
                    |--------------------------------------------------------------------------
                    */

                    $start = microtime(true);

                    logger()->info(
                        'Video processing: BGM download START',
                        [
                            'video_id' => $video->id,
                            'bgm_id' => $bgm->id,
                            'bgm_path' => $bgm->file_path,
                        ]
                    );

                    $bgmPath =
                        $this->storage->downloadToLocal(
                            $bgm->file_path
                        );

                    logger()->info(
                        'Video processing: BGM download DONE',
                        [
                            'video_id' => $video->id,
                            'bgm_id' => $bgm->id,
                            'bgm_path' => $bgm->file_path,
                            'local_path' => $bgmPath,
                            'elapsed_seconds' => round(
                                microtime(true) - $start,
                                3
                            ),
                            'file_size' => file_exists($bgmPath)
                                ? filesize($bgmPath)
                                : null,
                        ]
                    );

                    /*
                    |--------------------------------------------------------------------------
                    | VideoEditV2
                    |--------------------------------------------------------------------------
                    */

                    $options = $this->videoEditV2->build(
                        $logoPath,
                        $bgmPath,
                        $endingPath,
                        $videoDuration,
                        $videoWidth,
                        $videoHeight,
                        $metadata['has_audio']
                    );

                    logger()->info(
                        'Video processing: VideoEditV2 DONE',
                        [
                            'video_id' => $video->id,
                            'options' => $options,
                        ]
                    );

                break;

                case 'v3':

                    logger()->info(
                        'Video processing: VideoEditV3 START',
                        [
                            'video_id' => $video->id,
                        ]
                    );

                    $logoPath = storage_path(
                        'app/video/logo.png'
                    );

                    $endingPath = storage_path(
                        'app/video/ending.mp4'
                    );

                    /*
                    |--------------------------------------------------------------------------
                    | BGM
                    |--------------------------------------------------------------------------
                    */

                    if (!$video->bgm_id) {
                        throw new RuntimeException(
                            'V3ではBGMが選択されていません。'
                        );
                    }

                    $bgm = $video->bgm;

                    if (!$bgm) {
                        throw new RuntimeException(
                            '指定されたBGMが存在しません。'
                        );
                    }

                    $bgmPath =
                        $this->storage->downloadToLocal(
                            $bgm->file_path
                        );

                    /*
                    |--------------------------------------------------------------------------
                    | ナレーション生成
                    |--------------------------------------------------------------------------
                    |
                    | OpenAI
                    | ↓
                    | 原稿
                    | ↓
                    | Google Cloud TTS
                    | ↓
                    | MP3
                    |
                    */

                    $maxDuration = max(
                        1,
                        $videoDuration - 2
                    ); 

                    logger()->info(
                        'Video processing: Narration START',
                        [
                            'video_id' => $video->id,
                            'video_duration' => $videoDuration,
                            'max_narration_duration' => $maxDuration,
                        ]
                    );

                    $narration =
                        $this->narrationProcessor->process(
                            $video,
                            $maxDuration
                        );

                    $narrationPath =
                        $narration['audio_path'];

  

                    logger()->info(
                        'Video processing: Narration DONE',
                        [
                            'video_id' => $video->id,
                            'audio_path' => $narrationPath,
                            'text' => $narration['text'],
                        ]
                    );


                    /*
                    |--------------------------------------------------------------------------
                    | VideoEditV3
                    |--------------------------------------------------------------------------
                    */

                    $options =
                        $this->videoEditV3->build(
                            $logoPath,
                            $bgmPath,
                            $endingPath,
                            $narrationPath,
                            $narration['text'],
                            $videoDuration,
                            $narration['duration'],
                            $maxDuration,
                            $videoWidth,
                            $videoHeight,
                            $metadata['has_audio']
                        );

                    logger()->info(
                        'Video processing: VideoEditV3 DONE',
                        [
                            'video_id' => $video->id,
                            'options' => $options,
                        ]
                    );

                break;

                default:

                    throw new RuntimeException(
                        '未対応の動画加工バージョンです: '
                        . $video->edit_mode
                    );
            }

            /*
            |--------------------------------------------------------------------------
            | FFmpeg処理
            |--------------------------------------------------------------------------
            */

            $start = microtime(true);

            logger()->info(
                'Video processing: FFmpeg START',
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
                'Video processing: FFmpeg DONE',
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
            | S3保存パス生成
            |--------------------------------------------------------------------------
            */

            $processedPath =
                $this->storage->generatePath(
                    'shop',
                    'processed'
                );

            logger()->info(
                'Video processing: processed path generated',
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
                'Video processing: S3 upload START',
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
                'Video processing: S3 upload DONE',
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
            | S3ファイルサイズ取得
            |--------------------------------------------------------------------------
            */

            $start = microtime(true);

            logger()->info(
                'Video processing: S3 size START',
                [
                    'video_id' => $video->id,
                    's3_path' => $processedPath,
                ]
            );

            $fileSize = $this->storage->size(
                $processedPath
            );

            logger()->info(
                'Video processing: S3 size DONE',
                [
                    'video_id' => $video->id,
                    'file_size' => $fileSize,
                    'elapsed_seconds' => round(
                        microtime(true) - $start,
                        3
                    ),
                ]
            );

            /*
            |--------------------------------------------------------------------------
            | 全体処理完了
            |--------------------------------------------------------------------------
            */

            logger()->info(
                'Video processing DONE',
                [
                    'video_id' => $video->id,
                    'total_elapsed_seconds' => round(
                        microtime(true) - $totalStart,
                        3
                    ),
                    'processed_path' => $processedPath,
                    'duration' => $result['duration']
                        ?? $video->duration,
                    'file_size' => $fileSize,
                ]
            );

            /*
            |--------------------------------------------------------------------------
            | 結果
            |--------------------------------------------------------------------------
            */

            return [

                'processed_movie'
                    => $processedPath,

                'duration'
                    => $result['duration']
                        ?? $video->duration,

                'file_size'
                    => $fileSize,

            ];

        } finally {

                logger()->info(
                    'Video processing: temp file cleanup START',
                    [
                        'video_id' => $video->id,
                        'input_path' => $inputPath,
                        'bgm_path' => $bgmPath,
                        'output_path' => $outputPath,
                    ]
                );

                $this->storage->deleteTempFile(
                    $inputPath
                );

                $this->storage->deleteTempFile(
                    $bgmPath
                );

                $this->storage->deleteTempFile(
                    $outputPath
                );

                $this->storage->deleteTempFile(
                    $narrationPath
                );

                logger()->info(
                    'Video processing: temp file cleanup DONE',
                    [
                        'video_id' => $video->id,
                    ]
                );
            }
    }

    /**
     * 現在の既存処理
     */
    protected function copyAsProcessed(
        ShopVideoDraft $video
    ): array {

        $originalPath =
            $video->original_movie;

        $processedPath =
            $this->storage->generatePath(
                'shop',
                'processed'
            );

        $copied =
            $this->storage->copy(
                $originalPath,
                $processedPath
            );

        if (!$copied) {

            throw new RuntimeException(
                '処理済み動画の保存に失敗しました。'
            );
        }

        return [

            'processed_movie'
                => $processedPath,

            'duration'
                => $video->duration,

            'file_size'
                => $this->storage->size(
                    $processedPath
                ),

        ];
    }
}