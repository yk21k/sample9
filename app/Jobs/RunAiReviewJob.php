<?php

namespace App\Jobs;

use App\Models\Product;
use App\Models\ProductReviewQueue;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class RunAiReviewJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $productId;

    public function __construct($productId)
    {
        $this->productId = $productId;
    }

    public function handle()
    {
        $product = Product::find($this->productId);

        if (!$product) {
            return;
        }

        $queue = ProductReviewQueue::where('product_id', $product->id)->first();

        if (!$queue) {
            return;
        }

        // ダミーAI
        $result = [
            'adult' => rand(0, 5),
            'violence' => rand(0, 5),
            'score' => rand(30, 95)
        ];

        $score = $result['score'];

        $fixFields = [];
        $comment = null;

        if ($result['adult'] > 3) {
            $fixFields[] = 'cover_img';
            $comment = '不適切な可能性のある画像';
        }

        if ($result['violence'] > 3) {
            $fixFields[] = 'cover_img';
            $comment = '暴力的な可能性あり';
        }

        if ($score < 50) {
            $fixFields = array_unique(array_merge($fixFields, ['cover_img', 'movie']));
            $comment = '修正が必要です';
        }

        if ($result['adult'] > 4) {
            $score -= 30;
        }

        if ($result['violence'] > 4) {
            $score -= 20;
        }

        $queue->update([
            'ai_result' => $result,
            'ai_score' => $score,
            'ai_status' => 'done',
            'ai_checked_at' => now(),
            'fix_fields' => $fixFields,
            'comment' => $comment,
        ]);

        if ($score >= 85) {

            $product->update([
                'status' => 1,
                'review_status' => 'approved'
            ]);

            $queue->update(['status' => 'approved']);

        } elseif ($score >= 60) {

            $queue->update(['status' => 'pending_manual']);

        } else {

            $product->update([
                'status' => 0,
                'review_status' => 'rejected'
            ]);

            $queue->update(['status' => 'rejected']);
        }
    }
}
