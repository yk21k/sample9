<?php

namespace App\Services\Video\Processing;

use RuntimeException;

class LogoProcessor
{
    /**
     * ロゴ加工用のFFmpegオプションを生成
     *
     * ロゴ幅は元動画幅の20%。
     *
     * @return array{
     *     filter_complex: string,
     *     extra_inputs: array<int, string>,
     *     map_video: string,
     *     map_audio: string
     * }
     */
    public function build(
        string $logoPath
    ): array {

        if (!file_exists($logoPath)) {
            throw new RuntimeException(
                'ロゴファイルが存在しません: ' . $logoPath
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Logo Filter
        |--------------------------------------------------------------------------
        |
        | [1:v]
        |   ↓
        | RGBA化
        |   ↓
        | 元動画幅の20%にリサイズ
        |   ↓
        | [logo]
        |
        | [0:v]
        |   ↓
        | [base]
        |
        | 元動画 + logo
        |   ↓
        | 右下から40px
        |   ↓
        | [v]
        |
        */

        $filter =
            '[1:v]'
            . 'format=rgba'
            . '[logo_src];'

            . '[logo_src][0:v]'
            . 'scale2ref='
            . 'w=ref_w*0.20:'
            . 'h=-1'
            . '[logo][base];'

            . '[base][logo]'
            . 'overlay='
            . 'main_w-overlay_w-40:'
            . 'main_h-overlay_h-40'
            . '[v]';

        return [

            'filter_complex'
                => $filter,

            'extra_inputs'
                => [
                    $logoPath,
                ],

            'map_video'
                => '[v]',

            'map_audio'
                => '0:a?',

        ];
    }
}