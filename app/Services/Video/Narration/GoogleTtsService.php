<?php

namespace App\Services\Video\Narration;

use Google\Cloud\TextToSpeech\V1\AudioConfig;
use Google\Cloud\TextToSpeech\V1\AudioEncoding;
use Google\Cloud\TextToSpeech\V1\Client\TextToSpeechClient;
use Google\Cloud\TextToSpeech\V1\SynthesisInput;
use Google\Cloud\TextToSpeech\V1\VoiceSelectionParams;
use Google\Cloud\TextToSpeech\V1\SynthesizeSpeechRequest;
use RuntimeException;

class GoogleTtsService
{
    /**
     * Google Cloud Text-to-Speechで音声を生成
     */
    public function synthesize(
        string $text,
        string $outputPath
    ): string {

        if (trim($text) === '') {
            throw new RuntimeException(
                'TTSに渡すテキストが空です。'
            );
        }

        $client = new TextToSpeechClient();

        try {

            $input = new SynthesisInput();

            $input->setText(
                $text
            );

            /*
            |--------------------------------------------------------------------------
            | 日本語音声
            |--------------------------------------------------------------------------
            */

            $voice = new VoiceSelectionParams();

            $voice->setLanguageCode(
                'ja-JP'
            );

            /*
            |--------------------------------------------------------------------------
            | 音声設定
            |--------------------------------------------------------------------------
            */

            $audioConfig = new AudioConfig();

            $audioConfig->setAudioEncoding(
                AudioEncoding::MP3
            );

            /*
            |--------------------------------------------------------------------------
            | Request
            |--------------------------------------------------------------------------
            */

            $request = new SynthesizeSpeechRequest();

            $request->setInput(
                $input
            );

            $request->setVoice(
                $voice
            );

            $request->setAudioConfig(
                $audioConfig
            );

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

            if (!$audioContent) {

                throw new RuntimeException(
                    'Google Cloud TTSから音声データを取得できませんでした。'
                );
            }

            /*
            |--------------------------------------------------------------------------
            | 保存
            |--------------------------------------------------------------------------
            */

            $directory =
                dirname($outputPath);

            if (!is_dir($directory)) {

                mkdir(
                    $directory,
                    0775,
                    true
                );
            }

            file_put_contents(
                $outputPath,
                $audioContent
            );

            if (!file_exists($outputPath)) {

                throw new RuntimeException(
                    'TTS音声ファイルを保存できませんでした。'
                );
            }

            return $outputPath;

        } finally {

            $client->close();
        }
    }
}