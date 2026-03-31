<?php

namespace App\Jobs;

use App\Models\Product;
use App\Models\ProductReviewQueue;
use App\Services\RekognitionService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;


class AnalyzeProductImageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    public $product; 

    public function __construct(Product $product)
    {
        $this->product = $product;
    }

    public function handle()
    {
        Log::info('🔥 JOB START');

        try {
            $product = $this->product;
            $product->refresh(); // DBの最新状態を取得

            Log::info('PRODUCT ID: '.$product->id);

            Log::info('DB IMAGE VALUES', [
                'cover_img' => $product->cover_img,
                'cover_img2' => $product->cover_img2,
                'cover_img3' => $product->cover_img3,
                'movie' => $product->movie,
            ]);

            // 審査キュー作成 or 取得
            $queue = ProductReviewQueue::firstOrCreate(
                ['product_id' => $product->id],
                [
                    'user_id' => $product->shop->user_id ?? 1,
                    'status' => 'pending'
                ]
            );

            // 画像フィールド＋動画フィールドをループ
            $fields = ['cover_img', 'cover_img2', 'cover_img3'];
            $rekognition = new RekognitionService();
            $combinedResult = [];

            foreach ($fields as $field) {

                $s3Key = $product->{$field};

                // ① まず空チェック
                if (!$s3Key) {
                    Log::info("No file for field: {$field}");
                    continue;
                }

                // 🔥 ② ここに入れる（超重要）
                if (!\Storage::disk('s3')->exists($s3Key)) {
                    Log::error("❌ S3に存在しない: {$s3Key}");
                    continue;
                }

                Log::info("✅ S3存在確認OK: {$s3Key}");

                // ③ Rekognition実行
                try {
                    $labels = $rekognition->analyze($s3Key);

                    
                    foreach ($labels as $label) {
                        Log::info('DETECTED', [
                            'Name' => $label['Name'] ?? null,
                            'Confidence' => $label['Confidence'] ?? null,
                            'Parent' => $label['ParentName'] ?? null
                        ]);
                    }

                    if (!empty($labels)) {
                        $combinedResult = array_merge($combinedResult, $labels);
                    }

                } catch (\Throwable $e) {
                    Log::error("🔥 JOB ERROR for {$field}: ".$e->getMessage());
                }
            }

            // AI 判定
            // =========================
            // AI解析後
            // =========================

            $maxConfidence = collect($combinedResult)->max('Confidence') ?? 0;

            // ★ AI状態だけ管理
            // $aiStatus = empty($combinedResult) ? 'error' : 'done';
            // $aiStatus = count($combinedResult) === 0 ? 'no_issue' : 'detected';
            // $aiStatus = count($combinedResult) === 0 ? 'safe' : 'detected';

            $hasError = false;

            foreach ($fields as $field) {
                try {
                    $labels = $rekognition->analyze($s3Key);

                    if (!empty($labels)) {
                        $combinedResult = array_merge($combinedResult, $labels);
                    }

                } catch (\Throwable $e) {
                    $hasError = true;
                    Log::error("Rekognition error for {$s3Key}: ".$e->getMessage());
                }
            }

            $aiStatus = $hasError ? 'partial_error' : 'done';

            // =========================
            // 🔥 ここが重要（status触らない）
            // =========================

            $queue->update([
                'ai_result' => $combinedResult,
                'ai_score' => $maxConfidence,
                'ai_status' => $aiStatus,
                'ai_checked_at' => now()
            ]);

            Log::info('AI保存完了', [
                'score' => $maxConfidence,
                'status' => $aiStatus
            ]);

        } catch (\Throwable $e) {
            Log::error('🔥 JOB ERROR: '.$e->getMessage());
        }
    }

}
