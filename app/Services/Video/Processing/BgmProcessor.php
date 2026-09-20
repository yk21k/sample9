<?php

namespace App\Services\Video\Processing;

use RuntimeException;

class BgmProcessor
{
    /**
     * BGM用のFFmpegオプションを生成
     *
     * @return array{
     *     extra_inputs: array<int, string>,
     *     audio_filter: string
     * }
     */
    public function build(
        string $bgmPath,
        float $videoDuration,
        int $inputIndex = 2
    ): array {

        if ($videoDuration <= 0) {
            throw new RuntimeException(
                '動画時間が不正です。'
            );
        }

        if ($bgmPath === '') {
            throw new RuntimeException(
                'BGMファイルパスが設定されていません。'
            );
        }

        if (!file_exists($bgmPath)) {
            throw new RuntimeException(
                'BGMファイルがローカルに存在しません: '
                . $bgmPath
            );
        }

        /*
        |--------------------------------------------------------------------------
        | フェード
        |--------------------------------------------------------------------------
        */

        $fadeIn = 1.0;

        $fadeOut = min(
            2.0,
            $videoDuration
        );

        $fadeOutStart = max(
            0,
            $videoDuration - $fadeOut
        );

        $bgmVolume = 0.2;

        /*
        |--------------------------------------------------------------------------
        | Audio Filter
        |--------------------------------------------------------------------------
        |
        | BGMを動画全体の長さまでループ
        |
        */

        $audioFilter =
            '[' . $inputIndex . ':a]'
            . 'aloop=loop=-1:size=2e+09,'
            . 'atrim=duration='
            . $this->formatNumber($videoDuration)
            . ','
            . 'asetpts=N/SR/TB,'
            . 'volume='
            . $this->formatNumber($bgmVolume)
            . ','
            . 'afade=t=in:st=0:d='
            . $this->formatNumber($fadeIn)
            . ','
            . 'afade=t=out:st='
            . $this->formatNumber($fadeOutStart)
            . ':d='
            . $this->formatNumber($fadeOut)
            . '[bgm]';

        return [

            'extra_inputs' => [
                $bgmPath,
            ],

            'audio_filter' => $audioFilter,

        ];
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