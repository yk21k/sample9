<?php

namespace App\Console\Commands;

use App\Jobs\ProcessShopVideoJob;
use Illuminate\Console\Command;

class TestProcessShopVideo extends Command
{
    protected $signature = 'video:test-process
                            {video_id : ShopVideoDraftのID}';

    protected $description = 'ShopVideo V3 processing test';

    public function handle(): int
    {
        $videoId = (int) $this->argument('video_id');

        $this->info(
            "ProcessShopVideoJob dispatch: video_id={$videoId}"
        );

        ProcessShopVideoJob::dispatch($videoId);

        $this->info(
            'ProcessShopVideoJob dispatched successfully.'
        );

        return self::SUCCESS;
    }
}