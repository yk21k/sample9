<?php

namespace App\Services\Video\Processing;

use App\Models\ShopVideoDraft;
use App\Services\Video\Narration\NarrationGenerator;
use App\Services\Video\Narration\GoogleTtsService;
use RuntimeException;
use Illuminate\Support\Str;

class NarrationProcessor
{
    public function __construct(
        protected NarrationGenerator $generator,
        protected GoogleTtsService $tts,
    ) {
    }

    /**
     * ナレーション生成
     *
     * OpenAI
     * ↓
     * ナレーション原稿
     * ↓
     * Google Cloud TTS
     * ↓
     * ローカルMP3
     *
     * @return array{
     *     text: string,
     *     audio_path: string
     * }
     */
    public function process(
        ShopVideoDraft $video
    ): array {

        logger()->info(
            'Narration processing START',
            [
                'video_id' => $video->id,
            ]
        );

        /*
        |--------------------------------------------------------------------------
        | 1. ナレーション原稿生成
        |--------------------------------------------------------------------------
        */

        logger()->info(
            'Narration processing: text generation START',
            [
                'video_id' => $video->id,
            ]
        );

        $text = $this->generator->generate(
            $video
        );

        if (trim($text) === '') {
            throw new RuntimeException(
                'ナレーション原稿が空です。'
            );
        }

        logger()->info(
            'Narration processing: text generation DONE',
            [
                'video_id' => $video->id,
                'text_length' => mb_strlen($text),
                'text' => $text,
            ]
        );

        /*
        |--------------------------------------------------------------------------
        | 2. 保存先
        |--------------------------------------------------------------------------
        */

        $directory =
            storage_path(
                'app/video-temp'
            );

        if (!is_dir($directory)) {

            if (!mkdir(
                $directory,
                0775,
                true
            ) && !is_dir($directory)) {

                throw new RuntimeException(
                    'ナレーション保存ディレクトリを作成できません。'
                );
            }
        }

        $audioPath =
            $directory
            . '/narration_'
            . $video->id
            . '_'
            . Str::uuid()
            . '.mp3';

        /*
        |--------------------------------------------------------------------------
        | 3. Google Cloud TTS
        |--------------------------------------------------------------------------
        */

        logger()->info(
            'Narration processing: Google TTS START',
            [
                'video_id' => $video->id,
                'audio_path' => $audioPath,
            ]
        );

        $this->tts->synthesize(
            $text,
            $audioPath
        );

        /*
        |--------------------------------------------------------------------------
        | 4. ファイル確認
        |--------------------------------------------------------------------------
        */

        if (!file_exists($audioPath)) {

            throw new RuntimeException(
                'Google Cloud TTSで音声ファイルが生成されませんでした。'
            );
        }

        $fileSize =
            filesize($audioPath);

        if (!$fileSize || $fileSize <= 0) {

            throw new RuntimeException(
                '生成されたナレーション音声ファイルが空です。'
            );
        }

        logger()->info(
            'Narration processing: Google TTS DONE',
            [
                'video_id' => $video->id,
                'audio_path' => $audioPath,
                'file_size' => $fileSize,
            ]
        );

        /*
        |--------------------------------------------------------------------------
        | 完了
        |--------------------------------------------------------------------------
        */

        logger()->info(
            'Narration processing DONE',
            [
                'video_id' => $video->id,
                'audio_path' => $audioPath,
            ]
        );

        return [

            'text' =>
                $text,

            'audio_path' =>
                $audioPath,

        ];
    }
}