<?php

namespace App\Services\Video\Processing;

use Symfony\Component\Process\Process;
use RuntimeException;
use Illuminate\Support\Facades\Log;

class FfmpegVideoProcessor
{
    /**
     * 動画処理
     */
    public function process(
        string $inputPath,
        string $outputPath,
        array $options = []
    ): array {

        $ffmpeg = config(
            'video.ffmpeg_path'
        );

        if (!$ffmpeg) {
            throw new RuntimeException(
                'FFmpegのパスが設定されていません。'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | V2
        |--------------------------------------------------------------------------
        */

        if (
            ($options['processing_mode'] ?? null)
            === 'v2'
        ) {
            return $this->processV2(
                $ffmpeg,
                $inputPath,
                $outputPath,
                $options
            );
        }

        if (
            ($options['processing_mode'] ?? null)
            === 'v3'
        ) {
            return $this->processV3(
                $ffmpeg,
                $inputPath,
                $outputPath,
                $options
            );
        }

        /*
        |--------------------------------------------------------------------------
        | V1 / 既存処理
        |--------------------------------------------------------------------------
        */

        return $this->processBasic(
            $ffmpeg,
            $inputPath,
            $outputPath,
            $options
        );
    }

    /**
     * 既存のV1処理
     */
    protected function processBasic(
        string $ffmpeg,
        string $inputPath,
        string $outputPath,
        array $options
    ): array {

        $command = [
            $ffmpeg,
            '-y',
            '-i',
            $inputPath,
        ];

        /*
        |--------------------------------------------------------------------------
        | 追加入力
        |--------------------------------------------------------------------------
        */

        if (!empty($options['extra_inputs'])) {

            foreach (
                $options['extra_inputs']
                as $extraInput
            ) {

                $command[] = '-i';
                $command[] = $extraInput;
            }
        }

        logger()->info(
            'FFmpeg options received',
            [
                'options' => $options,
            ]
        );

        /*
        |--------------------------------------------------------------------------
        | Filter
        |--------------------------------------------------------------------------
        */

        if (!empty($options['filter_complex'])) {

            $command[] = '-filter_complex';
            $command[] = $options['filter_complex'];

        } elseif (!empty($options['video_filter'])) {

            $command[] = '-vf';
            $command[] = $options['video_filter'];
        }

        /*
        |--------------------------------------------------------------------------
        | Video
        |--------------------------------------------------------------------------
        */

        $command[] = '-c:v';
        $command[] = 'libx264';

        $command[] = '-preset';
        $command[] = 'veryfast';

        $command[] = '-crf';
        $command[] = '27';

        /*
        |--------------------------------------------------------------------------
        | Audio
        |--------------------------------------------------------------------------
        */

        $command[] = '-c:a';
        $command[] = 'aac';

        /*
        |--------------------------------------------------------------------------
        | Map
        |--------------------------------------------------------------------------
        */

        if (!empty($options['map_video'])) {

            $command[] = '-map';
            $command[] = $options['map_video'];
        }

        if (!empty($options['map_audio'])) {

            $command[] = '-map';
            $command[] = $options['map_audio'];
        }

        /*
        |--------------------------------------------------------------------------
        | Output
        |--------------------------------------------------------------------------
        */

        $command[] = $outputPath;

        return $this->run(
            $command,
            $inputPath,
            $outputPath,
            $options
        );
    }

    /**
     * V2処理
     */
    protected function processV2(
        string $ffmpeg,
        string $inputPath,
        string $outputPath,
        array $options
    ): array {

        $command = [
            $ffmpeg,
            '-y',

            /*
            |--------------------------------------------------------------------------
            | 元動画
            |--------------------------------------------------------------------------
            */

            '-i',
            $inputPath,
        ];

        /*
        |--------------------------------------------------------------------------
        | 追加入力
        |--------------------------------------------------------------------------
        */

        foreach (
            $options['extra_inputs'] ?? []
            as $extraInput
        ) {

            $command[] = '-i';
            $command[] = $extraInput;
        }

        /*
        |--------------------------------------------------------------------------
        | V2 Filter Graph
        |--------------------------------------------------------------------------
        */

        $filterComplex =
            $options['filter_complex']
            ?? null;

        if (!$filterComplex) {

            throw new RuntimeException(
                'V2のFilter Graphが設定されていません。'
            );
        }

        $command[] = '-filter_complex';
        $command[] = $filterComplex;

        /*
        |--------------------------------------------------------------------------
        | Video
        |--------------------------------------------------------------------------
        */

        $command[] = '-map';
        $command[] = $options['map_video'];

        /*
        |--------------------------------------------------------------------------
        | Audio
        |--------------------------------------------------------------------------
        */

        $command[] = '-map';
        $command[] = $options['map_audio'];

        /*
        |--------------------------------------------------------------------------
        | Video Codec
        |--------------------------------------------------------------------------
        */

        $command[] = '-c:v';
        $command[] = 'libx264';

        $command[] = '-preset';
        $command[] = 'veryfast';

        $command[] = '-crf';
        $command[] = '27';

        /*
        |--------------------------------------------------------------------------
        | Audio Codec
        |--------------------------------------------------------------------------
        */

        $command[] = '-c:a';
        $command[] = 'aac';

        /*
        |--------------------------------------------------------------------------
        | Output
        |--------------------------------------------------------------------------
        */

        $command[] = $outputPath;

        return $this->run(
            $command,
            $inputPath,
            $outputPath,
            $options
        );
    }

    /**
     * V3処理
     */
    protected function processV3(
        string $ffmpeg,
        string $inputPath,
        string $outputPath,
        array $options
    ): array {

        $command = [
            $ffmpeg,
            '-y',

            /*
            |--------------------------------------------------------------------------
            | 元動画
            |--------------------------------------------------------------------------
            */

            '-i',
            $inputPath,
        ];

        /*
        |--------------------------------------------------------------------------
        | 追加入力
        |--------------------------------------------------------------------------
        */

        foreach (
            $options['extra_inputs'] ?? []
            as $extraInput
        ) {

            $command[] = '-i';
            $command[] = $extraInput;
        }

        /*
        |--------------------------------------------------------------------------
        | Filter Graph
        |--------------------------------------------------------------------------
        */

        $filterComplex =
            $options['filter_complex']
            ?? null;

        if (!$filterComplex) {

            throw new RuntimeException(
                'V3のFilter Graphが設定されていません。'
            );
        }

        $command[] = '-filter_complex';
        $command[] = $filterComplex;

        /*
        |--------------------------------------------------------------------------
        | Video
        |--------------------------------------------------------------------------
        */

        $command[] = '-map';
        $command[] = $options['map_video'];

        /*
        |--------------------------------------------------------------------------
        | Audio
        |--------------------------------------------------------------------------
        */

        $command[] = '-map';
        $command[] = $options['map_audio'];

        /*
        |--------------------------------------------------------------------------
        | Video Codec
        |--------------------------------------------------------------------------
        */

        $command[] = '-c:v';
        $command[] = 'libx264';

        $command[] = '-preset';
        $command[] = 'veryfast';

        $command[] = '-crf';
        $command[] = '27';

        /*
        |--------------------------------------------------------------------------
        | Audio Codec
        |--------------------------------------------------------------------------
        */

        $command[] = '-c:a';
        $command[] = 'aac';

        /*
        |--------------------------------------------------------------------------
        | Output
        |--------------------------------------------------------------------------
        */

        $command[] = $outputPath;

        return $this->run(
            $command,
            $inputPath,
            $outputPath,
            $options
        );
    }

    /**
     * FFmpeg実行
     */
    protected function run(
        array $command,
        string $inputPath,
        string $outputPath,
        array $options
    ): array {

        $process = new Process(
            $command
        );

        $process->setTimeout(300);

        Log::info(
            'FFmpeg FINAL COMMAND',
            [
                'command'
                    => implode(' ', $command),
            ]
        );

        try {

            logger()->info(
                'FFmpeg processing started',
                [
                    'input' => $inputPath,
                    'output' => $outputPath,
                    'options' => $options,
                ]
            );

            $process->mustRun();

        } catch (\Throwable $e) {

            logger()->error(
                'FFmpeg video processing failed',
                [
                    'input' => $inputPath,
                    'output' => $outputPath,

                    'command'
                        => $process->getCommandLine(),

                    'error'
                        => $process->getErrorOutput(),

                ]
            );

            file_put_contents(
                storage_path('logs/ffmpeg-last-error.log'),
                $process->getErrorOutput()
            );

            throw $e;
        }

        if (!file_exists($outputPath)) {

            throw new RuntimeException(
                'FFmpeg処理後のファイルが作成されませんでした。'
            );
        }

        return [

            'output_path'
                => $outputPath,

            'duration'
                => $this->getDuration(
                    $outputPath
                ),

        ];
    }

    /**
     * 動画の長さを取得
     */
    protected function getDuration(
        string $path
    ): ?float {

        $ffprobe = config(
            'video.ffprobe_path'
        );

        if (!$ffprobe) {
            return null;
        }

        $process = new Process([
            $ffprobe,

            '-v',
            'error',

            '-show_entries',
            'format=duration',

            '-of',
            'default=noprint_wrappers=1:nokey=1',

            $path,
        ]);

        try {

            $process->mustRun();

            $duration = trim(
                $process->getOutput()
            );

            return $duration !== ''
                ? (float) $duration
                : null;

        } catch (\Throwable $e) {

            logger()->warning(
                'FFprobe duration detection failed',
                [
                    'path' => $path,
                    'error' => $e->getMessage(),
                ]
            );

            return null;
        }
    }
}