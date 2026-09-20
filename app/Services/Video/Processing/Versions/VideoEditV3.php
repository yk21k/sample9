<?php

namespace App\Services\Video\Processing\Versions;

use App\Services\Video\Processing\BgmProcessor;
use App\Services\Video\Processing\EndingProcessor;
use App\Services\Video\Processing\NarrationProcessor;
use App\Services\Video\Processing\SubtitleProcessor;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use RuntimeException;

class VideoEditV3
{
    public function __construct(
        protected BgmProcessor $bgmProcessor,
        protected EndingProcessor $endingProcessor,
        protected NarrationProcessor $narrationProcessor,
        protected SubtitleProcessor $subtitleProcessor
    ) {
    }

    /**
     * V3動画加工用FFmpegオプションを生成
     *
     * 入力:
     *
     * [0] 元動画
     * [1] ロゴ
     * [2] BGM
     * [3] エンディング
     * [4] ナレーション
     *
     * 字幕はASSファイルをFFmpeg subtitles filterから直接読み込む。
     *
     * 出力:
     *
     * [v] 最終動画
     * [audio] 元音声 + ナレーション + BGM
     */
    public function build(
        string $logoPath,
        string $bgmPath,
        string $endingPath,
        string $narrationPath,
        string $narrationText,
        float $videoDuration,
        float $narrationDuration,
        float $maxNarrationDuration,
        int $videoWidth,
        int $videoHeight,
        bool $hasAudio
    ): array {

        if ($videoDuration <= 0) {
            throw new RuntimeException(
                '動画時間が不正です。'
            );
        }

        if ($maxNarrationDuration <= 0) {
            throw new RuntimeException(
                'ナレーション最大時間が不正です。'
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

        if (!file_exists($narrationPath)) {
            throw new RuntimeException(
                'ナレーションファイルが存在しません: '
                . $narrationPath
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
                $finalDuration,
                2
            );

        /*
        |--------------------------------------------------------------------------
        | ナレーション
        |--------------------------------------------------------------------------
        */

        $narrationOptions =
            $this->narrationProcessor->build(
                $narrationPath,
                $maxNarrationDuration,
                4
            );

        /*
        |--------------------------------------------------------------------------
        | 字幕
        |--------------------------------------------------------------------------
        |
        | 字幕は元動画部分だけに表示する。
        |
        | maxNarrationDuration はすでに
        |
        |     videoDuration - 安全余白
        |
        | として呼び出し側から渡されているため、
        | Ending部分には字幕が入り込まない。
        |
        */

        $subtitlePath =
            storage_path(
                'app/video-temp/subtitle_'
                . Str::uuid()
                . '.ass'
            );

        $subtitleOptions =
            $this->subtitleProcessor->build(
                $narrationText,
                $narrationDuration,
                $videoWidth,
                $videoHeight,
                $subtitlePath
            );  



        /*
        |--------------------------------------------------------------------------
        | 字幕ファイルパス
        |--------------------------------------------------------------------------
        */

        $subtitleFile =
            $this->escapeSubtitlePath(
                $subtitleOptions['subtitle_path']
            );

   

        /*
        |--------------------------------------------------------------------------
        | Filter Graph
        |--------------------------------------------------------------------------
        */

        $filterComplex =

            /*
            |--------------------------------------------------------------------------
            | ロゴ
            |--------------------------------------------------------------------------
            */

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

            /*
            |--------------------------------------------------------------------------
            | 字幕
            |--------------------------------------------------------------------------
            |
            | 字幕はここで焼き込む。
            |
            | 重要:
            |
            |     [main] → subtitles → [main_subtitled]
            |
            | としてからEndingとconcatする。
            |
            | そのためEndingには字幕が表示されない。
            |
            */

            . '[main]'
            . 'setpts=PTS-STARTPTS,'
            . 'setsar=1,'
            . 'format=yuv420p'
            . '[main_base];'

            . '[main_base]'
            . 'subtitles=filename='
            . $subtitleFile
            . '[main_subtitled];'

            /*
            |--------------------------------------------------------------------------
            | エンディング
            |--------------------------------------------------------------------------
            */

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

            /*
            |--------------------------------------------------------------------------
            | 動画結合
            |--------------------------------------------------------------------------
            */

            . '[main_subtitled][ending]'
            . 'concat=n=2:v=1:a=0'
            . '[v];'

            /*
            |--------------------------------------------------------------------------
            | BGM
            |--------------------------------------------------------------------------
            */

            . $bgmOptions['audio_filter']
            . ';'

            /*
            |--------------------------------------------------------------------------
            | ナレーション
            |--------------------------------------------------------------------------
            */

            . $narrationOptions['audio_filter']
            . ';'

            /*
            |--------------------------------------------------------------------------
            | 音声ミックス
            |--------------------------------------------------------------------------
            */

            . (
                $hasAudio

                    ? '[0:a]'
                        . 'volume=1.0'
                        . '[original];'

                        . '[narration]'
                        . 'volume=1.0'
                        . '[narration_vol];'

                        . '[bgm]'
                        . 'volume=0.25'
                        . '[bgm_vol];'

                        . '[original][narration_vol][bgm_vol]'
                        . 'amix=inputs=3:'
                        . 'duration=longest:'
                        . 'normalize=0'
                        . '[audio]'

                    : '[narration]'
                        . 'volume=1.0'
                        . '[narration_vol];'

                        . '[bgm]'
                        . 'volume=0.25'
                        . '[bgm_vol];'

                        . '[narration_vol][bgm_vol]'
                        . 'amix=inputs=2:'
                        . 'duration=longest:'
                        . 'normalize=0'
                        . '[audio]'
            );

        Log::debug(
            'VideoEditV3 filter_complex',
            [
                'filter_complex' => $filterComplex,
            ]
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

            ...$narrationOptions['extra_inputs'],

        ];

        return [

            'processing_mode'
                => 'v3',

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

            /*
            |--------------------------------------------------------------------------
            | 字幕ファイル
            |--------------------------------------------------------------------------
            |
            | VideoProcessingService側で一時ファイルとして
            | 削除できるように返す。
            |
            */

            'subtitle_path'
                => $subtitleOptions['subtitle_path'],

        ];
    }

    /**
     * subtitles filter用パスエスケープ
     */
    protected function escapeSubtitlePath(
        string $path
    ): string {

        return str_replace(
            [
                '\\',
                ':',
                "'",
            ],
            [
                '\\\\',
                '\\:',
                "\\'",
            ],
            $path
        );
    }

    /**
     * FFmpeg用の数値フォーマット
     */
    protected function formatNumber(
        float $value
    ): string {
        return rtrim(
            rtrim(
                number_format(
                    $value,
                    3,
                    '.',
                    ''
                ),
                '0'
            ),
            '.'
        );
    }

}

