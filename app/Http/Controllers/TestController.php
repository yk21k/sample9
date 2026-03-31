<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\ProductReviewQueue;

class TestController extends Controller
{
    public function aiTest(Product $product)
    {
        $queue = ProductReviewQueue::firstOrCreate(
            ['product_id' => $product->id],
            [
                'status' => 'pending',
                'user_id' => auth()->id()
            ]
        );

        // ★ 強制更新
        $queue->ai_result = [
            'adult' => 5,
            'violence' => 3,
            'score' => 92
        ];
        $queue->ai_score = 92;
        $queue->ai_status = 'done';
        $queue->ai_checked_at = now();

        $queue->save();

        return response()->json([
            'message' => 'AIテスト更新OK',
            'queue' => $queue->fresh()
        ]);
    }

    public function aiJudge(Product $product)
    {
        $queue = ProductReviewQueue::firstOrCreate(
            ['product_id' => $product->id],
            [
                'status' => 'pending',
                'user_id' => auth()->id()
            ]
        );

        // ★ AI結果（仮）
        $queue->ai_result = [
            'adult' => 5,
            'violence' => 3,
            'score' => 30
        ];
        $queue->ai_score = 30;
        $queue->ai_status = 'done';
        $queue->ai_checked_at = now();

        // ★ ここが本題（自動判定）
        if ($queue->ai_score >= 80) {

            // ✅ 自動承認
            $product->update([
                'status' => 1,
                'review_status' => 'approved',
                'reviewed_by' => null, // AI
                'reviewed_at' => now()
            ]);

            $queue->update([
                'status' => 'approved',
                'reviewed_at' => now()
            ]);

        } elseif ($queue->ai_score < 50) {

            // ❌ 自動却下
            $product->update([
                'status' => 0,
                'review_status' => 'rejected',
                'reviewed_by' => null,
                'reviewed_at' => now(),
                'review_comment' => 'AI自動却下'
            ]);

            $queue->update([
                'status' => 'rejected',
                'reviewed_at' => now(),
                'comment' => 'AI自動却下'
            ]);

        } else {

            // ⚠️ 人間審査
            $queue->update([
                'status' => 'pending_manual'
            ]);
        }

        return response()->json([
            'message' => 'AI判定完了',
            'queue' => $queue->fresh(),
            'product' => $product->fresh()
        ]);
    }
}
