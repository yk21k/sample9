<?php

namespace App\Services\Video\Processing;

use App\Models\ShopVideoDraft;
use Google\Cloud\TextToSpeech\V1\AudioConfig;
use Google\Cloud\TextToSpeech\V1\AudioEncoding;
use Google\Cloud\TextToSpeech\V1\Client\TextToSpeechClient;
use Google\Cloud\TextToSpeech\V1\SynthesisInput;
use Google\Cloud\TextToSpeech\V1\SynthesizeSpeechRequest;
use Google\Cloud\TextToSpeech\V1\VoiceSelectionParams;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class NarrationProcessor
{
    /**
     * ナレーション生成
     *
     * OpenAI
     * ↓
     * ナレーション原稿
     * ↓
     * Google Cloud TTS
     * ↓
     * MP3
     *
     * @return array{
     *     text: string,
     *     audio_path: string
     * }
     */
    public function process(
        ShopVideoDraft $video,
        float $maxDuration
    ): array {

        /*
        |--------------------------------------------------------------------------
        | 全体開始
        |--------------------------------------------------------------------------
        */

        $start = microtime(true);

        Log::info(
            'NarrationProcessor START',
            [
                'video_id' => $video->id,
            ]
        );

        /*
        |--------------------------------------------------------------------------
        | 原稿生成
        |--------------------------------------------------------------------------
        */

        $text = $this->generateScript(
            $video,
            $maxDuration
        );

        if ($text === '') {
            throw new RuntimeException(
                'ナレーション原稿が生成されませんでした。'
            );
        }

        Log::info(
            'NarrationProcessor: script generated',
            [
                'video_id' => $video->id,
                'text' => $text,
            ]
        );

        /*
        |--------------------------------------------------------------------------
        | 出力先
        |--------------------------------------------------------------------------
        */

        $audioPath =
            storage_path(
                'app/video-temp/narration_' .
                $video->id .
                '_' .
                \Illuminate\Support\Str::uuid() .
                '.mp3'
            );

        /*
        |--------------------------------------------------------------------------
        | Google Cloud TTS
        |--------------------------------------------------------------------------
        */

        $this->synthesize(
            $text,
            $audioPath
        );

        if (!file_exists($audioPath)) {
            throw new RuntimeException(
                'ナレーション音声ファイルが生成されませんでした。'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | 実際のナレーション時間
        |--------------------------------------------------------------------------
        */

        $actualDuration =
            $this->getAudioDuration(
                $audioPath
            );

        if ($actualDuration <= 0) {
            throw new RuntimeException(
                'ナレーション音声の長さを取得できませんでした。'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | 完了
        |--------------------------------------------------------------------------
        */

        Log::info(
            'NarrationProcessor DONE',
            [
                'video_id' => $video->id,
                'audio_path' => $audioPath,
                'file_size' => filesize($audioPath),
                'elapsed_seconds' => round(
                    microtime(true) - $start,
                    3
                ),
            ]
        );

        return [

            'text'
                => $text,

            'audio_path'
                => $audioPath,

            'duration'
                => $actualDuration,    

        ];
    }

    /**
     * OpenAIでナレーション原稿を生成
     */
    protected function generateScript(
        ShopVideoDraft $video,
        float $maxDuration
    ): string {

        $apiKey = config(
            'services.openai.api_key'
        );

        if (!$apiKey) {
            throw new RuntimeException(
                'OpenAI APIキーが設定されていません。'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | 動画情報
        |--------------------------------------------------------------------------
        */

        $title =
            trim((string) $video->title);

        $description =
            trim((string) $video->description);

        /*
        |--------------------------------------------------------------------------
        | プロンプト
        |--------------------------------------------------------------------------
        */

        $prompt = <<<PROMPT
        あなたは店舗紹介動画のナレーション原稿を作成するプロフェッショナルです。

        以下の店舗紹介動画情報をもとに、日本語のナレーション原稿を作成してください。

        【動画タイトル】
        {$title}

        【動画説明】
        {$description}

        【条件】
        - 店舗紹介動画として自然な文章にする
        - 視聴者に店舗や商品の魅力が伝わる内容にする
        - 誇張表現や事実にない情報を追加しない
        - 商品価格、営業時間、住所など、入力情報にない情報を勝手に追加しない
        - SNS向けなので簡潔にする
        - 文章だけを出力する
        - 「ナレーション原稿：」などの見出しは付けない
        - ナレーションの読み上げ時間は最大{$maxDuration}秒以内に収まるようにする
        - 日本語の自然な読み上げ速度を前提とする
        - {$maxDuration}秒を超える可能性がある長い文章は作らない
        - 内容を詰め込みすぎず、短く簡潔にする
        PROMPT;

        /*
        |--------------------------------------------------------------------------
        | OpenAI Responses API
        |--------------------------------------------------------------------------
        */

        $response = Http::withToken(
            $apiKey
        )
        ->timeout(60)
        ->post(
            'https://api.openai.com/v1/responses',
            [
                'model' =>
                    config(
                        'services.openai.narration_model',
                        'gpt-5-mini'
                    ),

                'input' => $prompt,

                'store' => false,
            ]
        );

        if ($response->failed()) {

            Log::error(
                'OpenAI narration generation failed',
                [
                    'video_id' => $video->id,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]
            );

            throw new RuntimeException(
                'OpenAIによるナレーション原稿生成に失敗しました。'
            );
        }

        $data = $response->json();


        /*
        |--------------------------------------------------------------------------
        | output_text
        |--------------------------------------------------------------------------
        */

        $text = '';

        foreach ($data['output'] ?? [] as $output) {

            if (
                ($output['type'] ?? null) !== 'message'
            ) {
                continue;
            }

            foreach (
                $output['content'] ?? []
                as $content
            ) {

                if (
                    ($content['type'] ?? null)
                    !== 'output_text'
                ) {
                    continue;
                }

                $text .=
                    (string) (
                        $content['text'] ?? ''
                    );
            }
        }

        $text = trim($text);

        if ($text === '') {

            Log::error(
                'OpenAI narration response has no output text',
                [
                    'video_id' => $video->id,
                    'response' => $data,
                ]
            );

            throw new RuntimeException(
                'OpenAIからナレーション原稿を取得できませんでした。'
            );
        }

        return $text;
    }

    /**
     * Google Cloud TTSで音声生成
     */
    protected function synthesize(
        string $text,
        string $outputPath
    ): void {

        /*
        |--------------------------------------------------------------------------
        | Google Cloud TTS Client
        |--------------------------------------------------------------------------
        */

        $client = new TextToSpeechClient();

        try {

            /*
            |--------------------------------------------------------------------------
            | 入力
            |--------------------------------------------------------------------------
            */

            $input =
                (new SynthesisInput())
                ->setText($text);

            /*
            |--------------------------------------------------------------------------
            | Voice
            |--------------------------------------------------------------------------
            */

            $voice =
                (new VoiceSelectionParams())
                ->setLanguageCode(
                    config(
                        'services.google_tts.language_code',
                        'ja-JP'
                    )
                )
                ->setName(
                    config(
                        'services.google_tts.voice',
                        ''
                    )
                );

            /*
            |--------------------------------------------------------------------------
            | Audio
            |--------------------------------------------------------------------------
            */

            $audioConfig =
                (new AudioConfig())
                ->setAudioEncoding(
                    AudioEncoding::MP3
                )
                ->setSpeakingRate(
                    (float) config(
                        'services.google_tts.speaking_rate',
                        1.0
                    )
                )
                ->setPitch(
                    (float) config(
                        'services.google_tts.pitch',
                        0.0
                    )
                );

            /*
            |--------------------------------------------------------------------------
            | Request
            |--------------------------------------------------------------------------
            */

            $request =
                (new SynthesizeSpeechRequest())
                ->setInput($input)
                ->setVoice($voice)
                ->setAudioConfig($audioConfig);

            /*
            |--------------------------------------------------------------------------
            | TTS
            |--------------------------------------------------------------------------
            */

            $response =
                $client->synthesizeSpeech(
                    $request
                );

            $audioContent =
                $response->getAudioContent();

            if (
                !$audioContent ||
                strlen($audioContent) === 0
            ) {
                throw new RuntimeException(
                    'Google Cloud TTSから音声データが返されませんでした。'
                );
            }

            /*
            |--------------------------------------------------------------------------
            | 保存
            |--------------------------------------------------------------------------
            */

            $directory =
                dirname($outputPath);

            if (
                !is_dir($directory) &&
                !mkdir($directory, 0775, true) &&
                !is_dir($directory)
            ) {
                throw new RuntimeException(
                    'ナレーション出力ディレクトリを作成できません。'
                );
            }

            file_put_contents(
                $outputPath,
                $audioContent
            );

        } finally {

            $client->close();
        }
    }

    
    /**
     * FFmpeg用のナレーションFilterを生成
     *
     * ナレーション原稿は生成時点で
     * 最大ナレーション時間以内に収まるように作成する。
     *
     * さらにFFmpeg側でも上限時間を設定し、
     * TTS音声が想定より長くなった場合でも
     * エンディングまでナレーションが伸びないようにする。
     *
     * @return array{
     *     extra_inputs: array<int, string>,
     *     audio_filter: string
     * }
     */
    public function build(
        string $narrationPath,
        float $maxNarrationDuration,
        int $inputIndex = 4
    ): array {

        if ($narrationPath === '') {
            throw new RuntimeException(
                'ナレーションファイルパスが設定されていません。'
            );
        }

        if (!file_exists($narrationPath)) {
            throw new RuntimeException(
                'ナレーションファイルが存在しません: '
                . $narrationPath
            );
        }

        if ($maxNarrationDuration <= 0) {
            throw new RuntimeException(
                'ナレーション最大時間が不正です。'
            );
        }

        $audioFilter =
            '[' . $inputIndex . ':a]'
            . 'atrim=duration='
            . $this->formatNumber($maxNarrationDuration)
            . ','
            . 'asetpts=N/SR/TB'
            . '[narration]';

        return [
            'extra_inputs' => [
                $narrationPath,
            ],

            'audio_filter' => $audioFilter,
        ];
    }


    /**
     * FFmpeg用数値フォーマット
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

    /**
     * 音声ファイルの長さを取得
     */
    protected function getAudioDuration(
        string $path
    ): float {

        $ffprobe = config(
            'video.ffprobe_path'
        );

        if (!$ffprobe) {
            throw new RuntimeException(
                'FFprobeのパスが設定されていません。'
            );
        }

        $process = new \Symfony\Component\Process\Process([
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

            $duration =
                trim(
                    $process->getOutput()
                );

            if ($duration === '') {
                throw new RuntimeException(
                    'FFprobeから音声時間を取得できませんでした。'
                );
            }

            return (float) $duration;

        } catch (\Throwable $e) {

            Log::error(
                'Narration audio duration detection failed',
                [
                    'path' => $path,
                    'error' => $e->getMessage(),
                ]
            );

            throw new RuntimeException(
                'ナレーション音声の長さ取得に失敗しました。',
                0,
                $e
            );
        }
    }
    
}