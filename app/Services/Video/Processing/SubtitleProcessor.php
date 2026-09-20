<?php

namespace App\Services\Video\Processing;

use RuntimeException;

class SubtitleProcessor
{
    /**
     * 字幕ファイルを生成
     *
     * ナレーション時間内だけ字幕を表示する。
     * Ending部分には字幕を表示しない。
     *
     * 横長・縦長の両方に対応する。
     *
     * @return array{
     *     subtitle_path: string
     * }
     */
    public function build(
        string $text,
        float $duration,
        int $videoWidth,
        int $videoHeight,
        string $outputPath
    ): array {

        if (trim($text) === '') {
            throw new RuntimeException(
                '字幕テキストが設定されていません。'
            );
        }

        if ($duration <= 0) {
            throw new RuntimeException(
                '字幕表示時間が不正です。'
            );
        }

        if ($videoWidth <= 0 || $videoHeight <= 0) {
            throw new RuntimeException(
                '動画サイズが不正です。'
            );
        }

        $directory = dirname($outputPath);

        if (
            !is_dir($directory) &&
            !mkdir($directory, 0775, true) &&
            !is_dir($directory)
        ) {
            throw new RuntimeException(
                '字幕出力ディレクトリを作成できません。'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | ASS字幕
        |--------------------------------------------------------------------------
        */

        $ass = $this->buildAss(
            $text,
            $duration,
            $videoWidth,
            $videoHeight
        );

        if (
            file_put_contents(
                $outputPath,
                $ass
            ) === false
        ) {
            throw new RuntimeException(
                '字幕ファイルを生成できませんでした。'
            );
        }

        return [
            'subtitle_path' => $outputPath,
        ];
    }

    /**
     * ASS字幕を生成
     */
    protected function buildAss(
        string $text,
        float $duration,
        int $videoWidth,
        int $videoHeight
    ): string {

        $isPortrait =
            $videoHeight > $videoWidth;

        if ($isPortrait) {

            $playResX = 360;
            $playResY = 640;

            $fontSize = 30;
            $marginL = 20;
            $marginR = 20;
            $marginV = 35;

        } else {

            $playResX = 640;
            $playResY = 360;

            $fontSize = 30;
            $marginL = 30;
            $marginR = 30;
            $marginV = 25;
        }

        $ass = <<<ASS
    [Script Info]
    ScriptType: v4.00+
    PlayResX: {$playResX}
    PlayResY: {$playResY}
    ScaledBorderAndShadow: yes

    [V4+ Styles]
    Format: Name, Fontname, Fontsize, PrimaryColour, SecondaryColour, OutlineColour, BackColour, Bold, Italic, Underline, StrikeOut, ScaleX, ScaleY, Spacing, Angle, BorderStyle, Outline, Shadow, Alignment, MarginL, MarginR, MarginV, Encoding
    Style: Default,Noto Sans CJK JP,{$fontSize},&H00FFFFFF,&H00FFFFFF,&H00000000,&H80000000,0,0,0,0,100,100,0,0,1,3,1,2,{$marginL},{$marginR},{$marginV},1

    [Events]
    Format: Layer, Start, End, Style, Name, MarginL, MarginR, MarginV, Effect, Text

    ASS;

        /*
        |--------------------------------------------------------------------------
        | 字幕を意味単位に分割
        |--------------------------------------------------------------------------
        */

        $segments =
            $this->splitSubtitleSegments(
                $text
            );

        if (empty($segments)) {
            return $ass;
        }

        /*
        |--------------------------------------------------------------------------
        | 各字幕の時間を文字数比率で配分
        |--------------------------------------------------------------------------
        */

        $totalChars = 0;

        foreach ($segments as $segment) {

            $totalChars +=
                mb_strlen(
                    $segment,
                    'UTF-8'
                );
        }

        if ($totalChars <= 0) {
            return $ass;
        }

        $currentTime = 0.0;

        foreach ($segments as $index => $segment) {

            $charCount =
                mb_strlen(
                    $segment,
                    'UTF-8'
                );

            $segmentDuration =
                $duration
                * ($charCount / $totalChars);

            /*
            |--------------------------------------------------------------------------
            | 最終字幕はdurationまで確実に合わせる
            |--------------------------------------------------------------------------
            */

            if (
                $index === count($segments) - 1
            ) {

                $endTime = $duration;

            } else {

                $endTime =
                    $currentTime
                    + $segmentDuration;
            }

            /*
            |--------------------------------------------------------------------------
            | 画面内改行
            |--------------------------------------------------------------------------
            */

            $subtitleText =
                $this->formatText(
                    $segment,
                    $isPortrait
                );

            $ass .=
                'Dialogue: 0,'
                . $this->formatAssTime(
                    $currentTime
                )
                . ','
                . $this->formatAssTime(
                    $endTime
                )
                . ',Default,,0,0,0,,'
                . $subtitleText
                . PHP_EOL;

            $currentTime =
                $endTime;
        }

        return $ass;
    }

    /**
     * 字幕を意味単位に分割
     *
     * 句点・感嘆符・疑問符を基本的な
     * 字幕切り替えポイントとする。
     *
     * 1つの字幕が極端に長くならないように、
     * 句読点がない場合は一定文字数で分割する。
     *
     * @return array<int, string>
     */
    protected function splitSubtitleSegments(
        string $text
    ): array {

        $text = trim($text);

        if ($text === '') {
            return [];
        }

        /*
        |--------------------------------------------------------------------------
        | 改行を空白に統一
        |--------------------------------------------------------------------------
        */

        $text = preg_replace(
            '/\R/u',
            ' ',
            $text
        );

        $text = preg_replace(
            '/[ \t]+/u',
            ' ',
            $text
        );

        $text = trim($text);

        /*
        |--------------------------------------------------------------------------
        | 句点などで分割
        |--------------------------------------------------------------------------
        |
        | 句読点自体は前の字幕に残す。
        |
        */

        $parts = preg_split(
            '/(?<=[。！？!?])/u',
            $text,
            -1,
            PREG_SPLIT_NO_EMPTY
        );

        if (!$parts) {
            return [$text];
        }

        $segments = [];

        foreach ($parts as $part) {

            $part = trim($part);

            if ($part === '') {
                continue;
            }

            /*
            |--------------------------------------------------------------------------
            | 長すぎる字幕を分割
            |--------------------------------------------------------------------------
            */

            $chunks =
                $this->splitLongSegment(
                    $part
                );

            foreach ($chunks as $chunk) {

                $chunk = trim($chunk);

                if ($chunk !== '') {
                    $segments[] = $chunk;
                }
            }
        }

        return $segments;
    }

    /**
     * 長すぎる字幕を分割
     *
     * 句点がない長文でも字幕が長時間
     * 画面に残り続けないようにする。
     *
     * @return array<int, string>
     */
    protected function splitLongSegment(
        string $text
    ): array {

        $maxChars = 28;

        if (
            mb_strlen(
                $text,
                'UTF-8'
            ) <= $maxChars
        ) {
            return [$text];
        }

        $segments = [];

        $remaining = $text;

        while (
            mb_strlen(
                $remaining,
                'UTF-8'
            ) > $maxChars
        ) {

            $splitAt =
                $this->findSplitPosition(
                    $remaining,
                    $maxChars
                );

            if ($splitAt <= 0) {
                $splitAt = $maxChars;
            }

            $segments[] =
                trim(
                    mb_substr(
                        $remaining,
                        0,
                        $splitAt,
                        'UTF-8'
                    )
                );

            $remaining =
                trim(
                    mb_substr(
                        $remaining,
                        $splitAt,
                        null,
                        'UTF-8'
                    )
                );
        }

        if ($remaining !== '') {
            $segments[] = $remaining;
        }

        return $segments;
    }

    /**
     * 字幕テキスト整形
     *
     * 横長・縦長で1行に入れる文字数を変える。
     */
    protected function formatText(
        string $text,
        bool $isPortrait
    ): string {

        $text = trim($text);

        /*
        |--------------------------------------------------------------------------
        | ASS特殊文字
        |--------------------------------------------------------------------------
        */

        $text = str_replace(
            [
                '{',
                '}',
            ],
            [
                '\\{',
                '\\}',
            ],
            $text
        );

        /*
        |--------------------------------------------------------------------------
        | 既存改行
        |--------------------------------------------------------------------------
        */

        $text = preg_replace(
            '/\R/u',
            '\\N',
            $text
        );

        /*
        |--------------------------------------------------------------------------
        | すでに改行されている場合
        |--------------------------------------------------------------------------
        */

        if (strpos($text, '\\N') !== false) {
            return $text;
        }

        /*
        |--------------------------------------------------------------------------
        | 1行あたりの目安文字数
        |--------------------------------------------------------------------------
        |
        | 縦長は横幅が狭いため少なめ。
        |
        */

        $maxCharsPerLine =
            $isPortrait
                ? 14
                : 24;

        /*
        |--------------------------------------------------------------------------
        | 改行不要
        |--------------------------------------------------------------------------
        */

        if (
            mb_strlen(
                $text,
                'UTF-8'
            ) <= $maxCharsPerLine
        ) {
            return $text;
        }

        /*
        |--------------------------------------------------------------------------
        | 文章を自然な位置で分割
        |--------------------------------------------------------------------------
        */

        return $this->splitText(
            $text,
            $maxCharsPerLine
        );
    }

    /**
     * 日本語字幕を自然な位置で改行
     */
    protected function splitText(
        string $text,
        int $maxCharsPerLine
    ): string {

        $length =
            mb_strlen(
                $text,
                'UTF-8'
            );

        /*
        |--------------------------------------------------------------------------
        | 2行に収まる場合
        |--------------------------------------------------------------------------
        */

        $maxTotal =
            $maxCharsPerLine * 2;

        if ($length <= $maxTotal) {

            $splitAt =
                $this->findSplitPosition(
                    $text,
                    $maxCharsPerLine
                );

            return
                mb_substr(
                    $text,
                    0,
                    $splitAt,
                    'UTF-8'
                )
                . '\\N'
                .
                mb_substr(
                    $text,
                    $splitAt,
                    null,
                    'UTF-8'
                );
        }

        /*
        |--------------------------------------------------------------------------
        | 3行以上必要な長文
        |--------------------------------------------------------------------------
        |
        | 今回は字幕が画面からはみ出さないことを
        | 優先して最大2行にする。
        |
        */

        $first =
            mb_substr(
                $text,
                0,
                $maxCharsPerLine,
                'UTF-8'
            );

        $remaining =
            mb_substr(
                $text,
                $maxCharsPerLine,
                null,
                'UTF-8'
            );

        return
            $first
            . '\\N'
            . mb_substr(
                $remaining,
                0,
                $maxCharsPerLine,
                'UTF-8'
            );
    }

    /**
     * 自然な改行位置を探す
     */
    protected function findSplitPosition(
        string $text,
        int $target
    ): int {

        $length =
            mb_strlen(
                $text,
                'UTF-8'
            );

        $start =
            min(
                $target,
                $length
            );

        /*
        |--------------------------------------------------------------------------
        | 助詞・句読点などを優先
        |--------------------------------------------------------------------------
        */

        $preferredChars = [
            '。',
            '、',
            '！',
            '？',
            '!',
            '?',
            '」',
            '』',
            ' ',
        ];

        /*
        |--------------------------------------------------------------------------
        | target付近から後方検索
        |--------------------------------------------------------------------------
        */

        for (
            $i = $start;
            $i >= max(1, $start - 6);
            $i--
        ) {

            $char =
                mb_substr(
                    $text,
                    $i - 1,
                    1,
                    'UTF-8'
                );

            if (
                in_array(
                    $char,
                    $preferredChars,
                    true
                )
            ) {
                return $i;
            }
        }

        /*
        |--------------------------------------------------------------------------
        | 適切な位置がない場合
        |--------------------------------------------------------------------------
        */

        return $start;
    }

    /**
     * ASS時間フォーマット
     *
     * ASS:
     * H:MM:SS.cc
     */
    protected function formatAssTime(
        float $seconds
    ): string {

        $seconds =
            max(
                0,
                $seconds
            );

        $hours =
            (int) floor(
                $seconds / 3600
            );

        $minutes =
            (int) floor(
                ($seconds - ($hours * 3600))
                / 60
            );

        $remaining =
            $seconds
            - ($hours * 3600)
            - ($minutes * 60);

        $wholeSeconds =
            (int) floor(
                $remaining
            );

        $centiseconds =
            (int) round(
                ($remaining - $wholeSeconds)
                * 100
            );

        if ($centiseconds >= 100) {
            $wholeSeconds++;
            $centiseconds = 0;
        }

        if ($wholeSeconds >= 60) {
            $wholeSeconds = 0;
            $minutes++;
        }

        if ($minutes >= 60) {
            $minutes = 0;
            $hours++;
        }

        return sprintf(
            '%d:%02d:%02d.%02d',
            $hours,
            $minutes,
            $wholeSeconds,
            $centiseconds
        );
    }
}