<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ProductReviewQueue;
use Carbon\Carbon;

class ReviewReset extends Command
{
    protected $signature = 'review:reset';

    protected $description = '審査ロックをタイムアウトで解除';

    public function handle()
    {

        $timeout = Carbon::now()->subMinutes(15);

        $count = ProductReviewQueue::where('status','reviewing')
            ->where('review_started_at','<',$timeout)
            ->update([
                'status' => 'pending',
                'reviewer_id' => null,
                'review_started_at' => null
            ]);

        $this->info("Unlocked {$count} review queues.");

    }
}
