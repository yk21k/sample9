<?php

namespace App\Console\Commands;

use App\Models\ShopVideoDraft;
use App\Services\Video\Processing\NarrationProcessor;
use Illuminate\Console\Command;
use RuntimeException;

class TestNarration extends Command
{
    protected $signature = 'video:test-narration
                            {video_id : ShopVideoDraftのID}';

    protected $description = 'NarrationProcessorの実動作テスト';

    public function handle(
        NarrationProcessor $narrationProcessor
    ): int {

        $videoId = (int) $this->argument('video_id');

        $video = ShopVideoDraft::find($videoId);

        if (!$video) {

            $this->error(
                "ShopVideoDraft ID {$videoId} が見つかりません。"
            );

            return self::FAILURE;
        }

        $this->info(
            "NarrationProcessor START: video_id={$video->id}"
        );

        try {

            $result =
                $narrationProcessor->process(
                    $video
                );

            $this->newLine();

            $this->info('NarrationProcessor SUCCESS');

            $this->line(
                '--- 原稿 ---'
            );

            $this->line(
                $result['text']
            );

            $this->newLine();

            $this->line(
                '--- 音声ファイル ---'
            );

            $this->line(
                $result['audio_path']
            );

            if (
                file_exists(
                    $result['audio_path']
                )
            ) {

                $this->line(
                    'file_size: '
                    . filesize(
                        $result['audio_path']
                    )
                    . ' bytes'
                );

            } else {

                $this->error(
                    '音声ファイルが存在しません。'
                );

                return self::FAILURE;
            }

            return self::SUCCESS;

        } catch (\Throwable $e) {

            $this->error(
                'NarrationProcessor FAILED'
            );

            $this->error(
                $e->getMessage()
            );

            return self::FAILURE;
        }
    }
}