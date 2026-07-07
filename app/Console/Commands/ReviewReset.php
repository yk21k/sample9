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
        // タイムアウト（分）
        $timeoutMinutes = 2;

        $timeout = Carbon::now()->subMinutes($timeoutMinutes);

        // 対象取得（ログ用）
        $targets = ProductReviewQueue::where('status','reviewing')
        ->where(function($q){
            $q->whereNull('reviewer_id') // ← 追加
              ->orWhereNotNull('reviewer_id');
        })
        ->where('review_started_at','<',$timeout)
        ->get();

        $count = $targets->count();

        // 更新
        if ($count > 0) {

            ProductReviewQueue::whereIn('id', $targets->pluck('id'))
                ->update([
                    'status' => 'pending',
                    'reviewer_id' => null,
                    'review_started_at' => null
                ]);
        }

        // ターミナル表示
        $this->info("Unlocked {$count} review queues.");

        // ログ出力
        if ($count > 0) {
            \Log::info('審査ロック解除', [
                'count' => $count,
                'product_ids' => $targets->pluck('product_id')->toArray(),
                'released_at' => now(),
            ]);
        }

        return 0;
    }
}