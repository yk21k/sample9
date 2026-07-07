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
use App\Models\ProductImageReview;
use App\Helpers\Audit;
use App\Services\ProductReviewFlowService;


class AnalyzeProductImageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    public $reviewId;

    public function __construct(int $reviewId)
    {
        $this->reviewId = $reviewId;
    }

    public function handle()
    {
        $review = ProductImageReview::findOrFail(
            $this->reviewId
        );

        $before = $review->toArray();

        Audit::log(

            action: 'ai_image_review_started',

            target: $review,

            before: null,

            after: [

                'image_type' => $review->image_type,

                'image_path' => $review->image_path,

            ],

            description: 'AI画像審査開始'

        );


        $s3Key = $review->image_path;

        $rekognition = new RekognitionService();

        $result = $rekognition->analyze(
            $s3Key
        );

        if (empty($result)) {

            $review->update([

                'status' => 'approved',

                'risk_score' => 0,

                'reviewed_at' => now(),
            ]);

            $review->refresh();

            Audit::log(

                action: 'ai_image_review_completed',

                target: $review,

                before: $before,

                after: $review->toArray(),

                description: 'AI画像審査OK'

            );

        } else {

            $review->update([

                'status' => 'rejected',

                'risk_score' => collect($result)
                    ->max('Confidence'),

                'moderation_labels' => json_encode($result),

                'reviewed_at' => now(),
            ]);

            $review->refresh();

            Audit::log(

                action: 'ai_image_review_rejected',

                target: $review,

                before: $before,

                after: $review->toArray(),

                description: 'AI画像審査NG'

            );

        }

        /*
        |--------------------------------------------------------------------------
        | AI画像レビュー集約
        |--------------------------------------------------------------------------
        */
        app(ProductReviewFlowService::class)
            ->checkImageReviews(
                $review->draft
            );
    }


}
