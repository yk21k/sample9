<?php

namespace App\Console\Commands;

use App\Jobs\StartProductVideoSocialDistributionJob;
use Illuminate\Console\Command;

class TestProductVideoSocialDistribution extends Command
{
    protected $signature = 'test:product-video-social {videoId}';

    protected $description = '商品SNS動画のSNS配信開始Jobをテスト投入';

    public function handle(): int
    {
        $videoId = (int) $this->argument('videoId');

        StartProductVideoSocialDistributionJob::dispatch(
            $videoId
        );

        $this->info(
            'SNS配信開始Jobをキューに投入しました。video_id='
            . $videoId
        );

        return self::SUCCESS;
    }
}