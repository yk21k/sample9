<?php

namespace App\Services\Video\Processing;

use RuntimeException;

class EndingProcessor
{
    /**
     * エンディング用のFFmpegオプションを生成
     *
     * V2では固定5秒のエンディングを使用する。
     *
     * @return array{
     *     extra_inputs: array<int, string>,
     *     duration: float
     * }
     */
    public function build(
        string $endingPath
    ): array {

        if (!file_exists($endingPath)) {
            throw new RuntimeException(
                'エンディングファイルが存在しません: '
                . $endingPath
            );
        }

        return [
            'extra_inputs' => [
                $endingPath,
            ],

            'duration' => 5.0,
        ];
    }
}