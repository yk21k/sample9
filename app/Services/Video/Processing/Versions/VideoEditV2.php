<?php

namespace App\Services\Video\Processing\Versions;

use App\Services\Video\Processing\BgmProcessor;
use App\Services\Video\Processing\EndingProcessor;
use RuntimeException;

class VideoEditV2
{
    public function __construct(
        protected BgmProcessor $bgmProcessor,
        protected EndingProcessor $endingProcessor
    ) {
    }

    /**
     * V2動画加工用FFmpegオプションを生成
     *
     * 入力:
     *
     * [0] 元動画
     * [1] ロゴ
     * [2] BGM
     * [3] エンディング
     *
     * 出力:
     *
     * [v] 最終動画
     * [bgm] 最終BGM
     *
     * @return array{
     *     processing_mode: string,
     *     extra_inputs: array<int, string>,
     *     filter_complex: string,
     *     map_video: string,
     *     map_audio: string,
     *     duration: float
     * }
     */
    public function build(
        string $logoPath,
        string $bgmPath,
        string $endingPath,
        float $videoDuration,
        int $videoWidth,
        int $videoHeight,
        bool $hasAudio
    ): array {

        /*
        |--------------------------------------------------------------------------
        | 基本チェック
        |--------------------------------------------------------------------------
        */

        if ($videoDuration <= 0) {
            throw new RuntimeException(
                '動画時間が不正です。'
            );
        }

        if (!file_exists($logoPath)) {
            throw new RuntimeException(
                'ロゴファイルが存在しません: '
                . $logoPath
            );
        }

        if (!file_exists($bgmPath)) {
            throw new RuntimeException(
                'BGMファイルが存在しません: '
                . $bgmPath
            );
        }

        if (!file_exists($endingPath)) {
            throw new RuntimeException(
                'エンディングファイルが存在しません: '
                . $endingPath
            );
        }

        /*
        |--------------------------------------------------------------------------
        | エンディング
        |--------------------------------------------------------------------------
        */

        $ending = $this->endingProcessor->build(
            $endingPath
        );

        $endingDuration =
            (float) $ending['duration'];

        if ($endingDuration <= 0) {
            throw new RuntimeException(
                'エンディング動画の長さを取得できません。'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | 最終動画時間
        |--------------------------------------------------------------------------
        */

        $finalDuration =
            $videoDuration
            + $endingDuration;

        /*
        |--------------------------------------------------------------------------
        | BGM
        |--------------------------------------------------------------------------
        */

        $bgmOptions =
            $this->bgmProcessor->build(
                $bgmPath,
                $finalDuration
            );

        /*
        |--------------------------------------------------------------------------
        | Filter Graph
        |--------------------------------------------------------------------------
        |
        | [0] 元動画
        | [1] ロゴ
        | [2] BGM
        | [3] エンディング
        |
        */

        $filterComplex =
            '[1:v]'
            . 'format=rgba'
            . '[logo_src];'

            . '[logo_src][0:v]'
            . 'scale2ref=w=ref_w*0.20:h=-1'
            . '[logo][base];'

            . '[base][logo]'
            . 'overlay='
            . 'main_w-overlay_w-40:'
            . 'main_h-overlay_h-40'
            . '[main];'

            . '[main]'
            . 'setpts=PTS-STARTPTS,'
            . 'setsar=1,'
            . 'format=yuv420p'
            . '[main_clean];'

            . '[3:v]'
            . 'scale='
            . $videoWidth . ':'
            . $videoHeight
            . ':force_original_aspect_ratio=increase,'
            . 'crop='
            . $videoWidth . ':'
            . $videoHeight . ','
            . 'setsar=1,'
            . 'format=yuv420p,'
            . 'setpts=PTS-STARTPTS'
            . '[ending];'

            . '[main_clean][ending]'
            . 'concat=n=2:v=1:a=0'
            . '[v];'

            . $bgmOptions['audio_filter']
            . ';'

            . (
                $hasAudio

                    ? '[0:a]'
                        . 'volume=1.0'
                        . '[voice];'

                        . '[voice][bgm]'
                        . 'amix=inputs=2:'
                        . 'duration=longest:'
                        . 'normalize=0'
                        . '[audio]'

                    : '[bgm]'
                        . 'volume=1.0'
                        . '[audio]'
            );

        /*
        |--------------------------------------------------------------------------
        | 入力
        |--------------------------------------------------------------------------
        */

        $extraInputs = [

            $logoPath,

            ...$bgmOptions['extra_inputs'],

            ...$ending['extra_inputs'],

        ];

        /*
        |--------------------------------------------------------------------------
        | 結果
        |--------------------------------------------------------------------------
        */

        return [

            'processing_mode'
                => 'v2',

            'extra_inputs'
                => $extraInputs,

            'filter_complex'
                => $filterComplex,

            'map_video'
                => '[v]',

            'map_audio'
                => '[audio]',

            'duration'
                => $finalDuration,

        ];
    }
}