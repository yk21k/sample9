<?php

namespace App\Console\Commands;

use App\Services\Video\Narration\GoogleTtsService;
use Illuminate\Console\Command;
use RuntimeException;

class TestVideoNarration extends Command
{
    protected $signature =
        'app:test-video-tts';

    protected $description =
        'Google Cloud Text-to-Speechのテスト';

    public function handle(
        GoogleTtsService $tts
    ) {

        $text =
            'こんにちは。こちらは店舗紹介動画のナレーションテストです。';

        $outputPath =
            storage_path(
                'app/video-temp/test-narration.mp3'
            );

        $this->info(
            'Google Cloud TTSで音声を生成しています...'
        );

        try {

            $tts->synthesize(
                $text,
                $outputPath
            );

            $this->info(
                '音声生成に成功しました。'
            );

            $this->info(
                '保存先: ' . $outputPath
            );

        } catch (\Throwable $e) {

            $this->error(
                '音声生成に失敗しました。'
            );

            $this->error(
                $e->getMessage()
            );

            return self::FAILURE;
        }

        return self::SUCCESS;
    }
}