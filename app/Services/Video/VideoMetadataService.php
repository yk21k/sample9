<?php

namespace App\Services\Video;

use RuntimeException;
use Symfony\Component\Process\Process;

class VideoMetadataService
{
    /**
     * 動画メタデータを取得する。
     *
     * @return array{
     *     width: int,
     *     height: int,
     *     duration: float,
     *     fps: float,
     *     video_codec: ?string,
     *     audio_codec: ?string,
     *     has_audio: bool,
     *     bitrate: ?int,
     *     file_size: int,
     *     mime_type: ?string,
     *     aspect_ratio: float
     * }
     */
    public function probe(string $inputPath): array
    {
        /*
        |--------------------------------------------------------------------------
        | ファイル確認
        |--------------------------------------------------------------------------
        */

        if (!file_exists($inputPath)) {
            throw new RuntimeException(
                '動画ファイルが存在しません: ' . $inputPath
            );
        }

        if (!is_readable($inputPath)) {
            throw new RuntimeException(
                '動画ファイルを読み込めません: ' . $inputPath
            );
        }

        /*
        |--------------------------------------------------------------------------
        | FFprobe
        |--------------------------------------------------------------------------
        */

        $ffprobe = config(
            'video.ffprobe_path',
            '/opt/homebrew/bin/ffprobe'
        );

        if (!file_exists($ffprobe)) {
            throw new RuntimeException(
                'FFprobeが存在しません: ' . $ffprobe
            );
        }

        /*
        |--------------------------------------------------------------------------
        | FFprobe実行
        |--------------------------------------------------------------------------
        */

        $process = new Process([
            $ffprobe,

            '-v',
            'error',

            '-show_entries',
            'stream=' .
                'codec_type,' .
                'width,' .
                'height,' .
                'r_frame_rate,' .
                'codec_name',

            '-show_entries',
            'format=' .
                'duration,' .
                'bit_rate,' .
                'format_name',

            '-of',
            'json',

            $inputPath,
        ]);

        $process->setTimeout(30);

        $process->run();

        /*
        |--------------------------------------------------------------------------
        | FFprobe失敗
        |--------------------------------------------------------------------------
        */

        if (!$process->isSuccessful()) {

            logger()->error(
                'FFprobe metadata probe failed',
                [
                    'input' => $inputPath,
                    'command' => $process->getCommandLine(),
                    'error' => $process->getErrorOutput(),
                ]
            );

            throw new RuntimeException(
                '動画メタデータの取得に失敗しました。'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | JSON解析
        |--------------------------------------------------------------------------
        */

        $data = json_decode(
            $process->getOutput(),
            true
        );

        if (!is_array($data)) {
            throw new RuntimeException(
                'FFprobeの出力を解析できません。'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | ストリーム
        |--------------------------------------------------------------------------
        */

        $streams = $data['streams'] ?? [];

        if (!is_array($streams)) {
            $streams = [];
        }

        /*
        |--------------------------------------------------------------------------
        | 映像ストリーム
        |--------------------------------------------------------------------------
        */

        $videoStream = null;

        foreach ($streams as $stream) {

            if (
                isset($stream['codec_type']) &&
                $stream['codec_type'] === 'video'
            ) {
                $videoStream = $stream;
                break;
            }
        }

        if (!$videoStream) {
            throw new RuntimeException(
                '動画ストリームが存在しません。'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | 音声ストリーム
        |--------------------------------------------------------------------------
        */

        $audioStream = null;

        foreach ($streams as $stream) {

            if (
                isset($stream['codec_type']) &&
                $stream['codec_type'] === 'audio'
            ) {
                $audioStream = $stream;
                break;
            }
        }

        /*
        |--------------------------------------------------------------------------
        | 幅・高さ
        |--------------------------------------------------------------------------
        */

        $width = (int) (
            $videoStream['width'] ?? 0
        );

        $height = (int) (
            $videoStream['height'] ?? 0
        );

        if ($width <= 0 || $height <= 0) {
            throw new RuntimeException(
                '動画の幅・高さを取得できません。'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | FPS
        |--------------------------------------------------------------------------
        */

        $fps = $this->parseFrameRate(
            $videoStream['r_frame_rate'] ?? null
        );

        if ($fps <= 0) {
            throw new RuntimeException(
                '動画のFPSを取得できません。'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | 再生時間
        |--------------------------------------------------------------------------
        */

        $duration = (float) (
            $data['format']['duration'] ?? 0
        );

        if ($duration <= 0) {
            throw new RuntimeException(
                '動画の再生時間を取得できません。'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | コーデック
        |--------------------------------------------------------------------------
        */

        $videoCodec =
            $videoStream['codec_name']
            ?? null;

        $audioCodec =
            $audioStream['codec_name']
            ?? null;

        $hasAudio =
            $audioStream !== null;

        /*
        |--------------------------------------------------------------------------
        | Bitrate
        |--------------------------------------------------------------------------
        */

        $bitrate = null;

        if (
            isset($data['format']['bit_rate']) &&
            is_numeric($data['format']['bit_rate'])
        ) {
            $bitrate = (int) $data['format']['bit_rate'];
        }

        /*
        |--------------------------------------------------------------------------
        | ファイルサイズ
        |--------------------------------------------------------------------------
        */

        $fileSize = filesize($inputPath);

        if ($fileSize === false) {
            throw new RuntimeException(
                '動画ファイルのサイズを取得できません。'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | MIME
        |--------------------------------------------------------------------------
        */

        $mimeType = null;

        if (function_exists('mime_content_type')) {

            $detectedMime =
                mime_content_type($inputPath);

            if (
                is_string($detectedMime) &&
                $detectedMime !== ''
            ) {
                $mimeType = $detectedMime;
            }
        }

        /*
        |--------------------------------------------------------------------------
        | アスペクト比
        |--------------------------------------------------------------------------
        */

        $aspectRatio =
            $height > 0
                ? $width / $height
                : 0;

        /*
        |--------------------------------------------------------------------------
        | 結果
        |--------------------------------------------------------------------------
        */

        $metadata = [

            'width'
                => $width,

            'height'
                => $height,

            'duration'
                => $duration,

            'fps'
                => $fps,

            'video_codec'
                => $videoCodec,

            'audio_codec'
                => $audioCodec,

            'has_audio'
                => $hasAudio,

            'bitrate'
                => $bitrate,

            'file_size'
                => $fileSize,

            'mime_type'
                => $mimeType,

            'aspect_ratio'
                => $aspectRatio,

        ];

        /*
        |--------------------------------------------------------------------------
        | ログ
        |--------------------------------------------------------------------------
        */

        logger()->info(
            'Video metadata probe DONE',
            [
                'input' => $inputPath,
                ...$metadata,
            ]
        );

        return $metadata;
    }

    /**
     * FFprobeのFPS表記をfloatへ変換する。
     *
     * 例:
     * 30/1     → 30.0
     * 30000/1001 → 29.970...
     * 24       → 24.0
     */
    protected function parseFrameRate(
        mixed $value
    ): float {

        if ($value === null) {
            return 0.0;
        }

        $value = trim((string) $value);

        if ($value === '') {
            return 0.0;
        }

        if (str_contains($value, '/')) {

            [$numerator, $denominator] =
                array_pad(
                    explode('/', $value, 2),
                    2,
                    null
                );

            if (
                is_numeric($numerator) &&
                is_numeric($denominator) &&
                (float) $denominator != 0.0
            ) {
                return
                    (float) $numerator /
                    (float) $denominator;
            }

            return 0.0;
        }

        return is_numeric($value)
            ? (float) $value
            : 0.0;
    }
}