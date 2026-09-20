<?php

namespace App\Services\Video\Workflow;

use App\Models\ProductVideoDraft;
use App\Helpers\Audit;
use App\Services\Video\Ai\ProductVideoAiService;
use App\Jobs\CheckProductVideoAiJob;
use App\Jobs\UploadProductVideoToYoutubeJob;
use App\Models\ProductVideoSocialPost;

class ProductVideoWorkflowService extends VideoWorkflowService
{
    /**
     * AI解析開始
     */
    public function startAi(
        ProductVideoDraft $video,
        ProductVideoAiService $videoAi
    ): void {

        $before = $video->toArray();

        $result = $videoAi->start(
            $video
        );

        $video->update([

            'workflow_stage'
                => ProductVideoDraft::STAGE_AI,

            'ai_status'
                => ProductVideoDraft::AI_PROCESSING,

            'ai_provider'
                => $result['provider'],

            'rekognition_job_id'
                => $result['job_id'],

        ]);

        Audit::log(

            action:
                'product_video_ai_started',

            target:
                $video,

            before:
                $before,

            after:
                $video->fresh()->toArray(),

            description:
                '商品SNS動画AI開始'

        );

        /*
        |--------------------------------------------------------------------------
        | Rekognition結果確認ジョブ
        |--------------------------------------------------------------------------
        */

        CheckProductVideoAiJob::dispatch(
            $video->id
        );
    }


    /**
     * AI解析完了
     */
    public function completeAi(
        ProductVideoDraft $video,
        array $result
    ): void {

        $before = $video->toArray();

        $video->update([

            'ai_status'
                => $result['status'],

            'risk_score'
                => $result['risk_score'],

            'ai_provider'
                => $result['provider'],

            'moderation_labels'
                => $result['labels'],

            'ai_reviewed_at'
                => now(),

        ]);

        $video = $video->fresh();

        /*
        |--------------------------------------------------------------------------
        | AI結果による次工程
        |--------------------------------------------------------------------------
        */

        switch ($video->ai_status) {

            case ProductVideoDraft::AI_APPROVED:

                $video->update([

                    'workflow_stage'
                        => ProductVideoDraft::STAGE_PROCESS,

                    'process_status'
                        => ProductVideoDraft::PROCESS_WAITING,

                ]);

                break;


            case ProductVideoDraft::AI_WARNING:


            case ProductVideoDraft::AI_REJECTED:

                $video->update([

                    'workflow_stage'
                        => ProductVideoDraft::STAGE_REVIEW,

                ]);

                break;
        }

        Audit::log(

            action:
                'product_video_ai_completed',

            target:
                $video,

            before:
                $before,

            after:
                $video->fresh()->toArray(),

            description:
                '商品SNS動画AI完了'

        );
    }


    /**
     * AI解析失敗
     */
    public function failAi(
        ProductVideoDraft $video,
        ?string $reason = null
    ): void {

        $before = $video->toArray();

        $video->update([

            'ai_status'
                => ProductVideoDraft::AI_FAILED,

        ]);

        Audit::log(

            action:
                'product_video_ai_failed',

            target:
                $video,

            before:
                $before,

            after:
                $video->fresh()->toArray(),

            description:
                '商品SNS動画AI失敗'
                . ($reason ? ': ' . $reason : '')

        );
    }


    /**
     * 動画加工開始
     */
    public function startProcessing(
        ProductVideoDraft $video
    ): void {

        $before = $video->toArray();

        $video->update([

            'workflow_stage'
                => ProductVideoDraft::STAGE_PROCESS,

            'process_status'
                => ProductVideoDraft::PROCESS_RUNNING,

        ]);

        Audit::log(

            action:
                'product_video_processing_started',

            target:
                $video,

            before:
                $before,

            after:
                $video->fresh()->toArray(),

            description:
                '商品SNS動画加工開始'

        );
    }


    /**
     * 動画加工完了
     */
    public function completeProcessing(
        ProductVideoDraft $video,
        array $result
    ): void {

        $before = $video->toArray();

        $video->update([

            'processed_movie'
                => $result['processed_movie'],

            'duration'
                => $result['duration'] ?? $video->duration,

            'file_size'
                => $result['file_size'] ?? null,

            'process_status'
                => ProductVideoDraft::PROCESS_COMPLETED,

            'processed_at'
                => now(),

            'workflow_stage'
                => ProductVideoDraft::STAGE_PREVIEW,

            'preview_status'
                => ProductVideoDraft::PREVIEW_WAITING,

        ]);

        Audit::log(

            action:
                'product_video_processing_completed',

            target:
                $video,

            before:
                $before,

            after:
                $video->fresh()->toArray(),

            description:
                '商品SNS動画加工完了'

        );
    }

    public function startPreview(
        ProductVideoDraft $video
    ): void {
        $before = $video->toArray();

        $video->update([
            'workflow_stage' => ProductVideoDraft::STAGE_PREVIEW,
            'preview_status' => ProductVideoDraft::PREVIEW_GENERATING,
        ]);

        Audit::log(
            action: 'product_video_preview_started',
            target: $video,
            before: $before,
            after: $video->fresh()->toArray(),
            description: '商品SNS動画Preview生成開始'
        );
    }

    public function completePreview(
        ProductVideoDraft $video,
        array $result
    ): void {
        $before = $video->toArray();

        $video->update([
            'preview_movie' => $result['preview_movie'],
            'thumbnail' => $result['thumbnail'],
            'preview_status' => ProductVideoDraft::PREVIEW_COMPLETED,
            'preview_generated_at' => now(),
            'workflow_stage' => ProductVideoDraft::STAGE_SELLER_REVIEW,
        ]);

        Audit::log(
            action: 'product_video_preview_completed',
            target: $video,
            before: $before,
            after: $video->fresh()->toArray(),
            description: '商品SNS動画Preview生成完了'
        );
    }

    public function failPreview(
        ProductVideoDraft $video,
        ?string $reason = null
    ): void {
        $before = $video->toArray();

        $video->update([
            'preview_status' => ProductVideoDraft::PREVIEW_FAILED,
        ]);

        Audit::log(
            action: 'product_video_preview_failed',
            target: $video,
            before: $before,
            after: $video->fresh()->toArray(),
            description: '商品SNS動画Preview生成失敗'
                . ($reason ? ': ' . $reason : '')
        );
    }


    /**
     * 動画加工失敗
     */
    public function failProcessing(
        ProductVideoDraft $video,
        ?string $reason = null
    ): void {

        $before = $video->toArray();

        $video->update([

            'process_status'
                => ProductVideoDraft::PROCESS_FAILED,

        ]);

        Audit::log(

            action:
                'product_video_processing_failed',

            target:
                $video,

            before:
                $before,

            after:
                $video->fresh()->toArray(),

            description:
                '商品SNS動画加工失敗'
                . ($reason ? ': ' . $reason : '')

        );
    }

    /**
     * 出品者確認完了
     */
    public function completeSellerReview(
        ProductVideoDraft $video,
        int $userId,
        ?string $comment = null
    ): void {

        $before = $video->toArray();

        $video->update([

            'seller_review_status'
                => ProductVideoDraft::SELLER_REVIEW_APPROVED,

            'seller_reviewed_at'
                => now(),

            'seller_reviewed_by'
                => $userId,

            'seller_review_comment'
                => $comment,

            'workflow_stage'
                => ProductVideoDraft::STAGE_REVIEW,

        ]);

        Audit::log(

            action:
                'product_video_seller_review_completed',

            target:
                $video,

            before:
                $before,

            after:
                $video->fresh()->toArray(),

            description:
                '商品SNS動画の出品者確認完了'

        );
    }

    public function approveReview(
        ProductVideoDraft $video,
        int $userId
    ): void {
        $before = $video->toArray();

        /*
        |--------------------------------------------------------------------------
        | 管理者審査 → YouTube工程への遷移条件
        |--------------------------------------------------------------------------
        */

        if (
            $video->ai_status !== ProductVideoDraft::AI_APPROVED
            ||
            $video->process_status !== ProductVideoDraft::PROCESS_COMPLETED
            ||
            $video->preview_status !== ProductVideoDraft::PREVIEW_COMPLETED
            ||
            $video->seller_review_status !== ProductVideoDraft::SELLER_REVIEW_APPROVED
            ||
            empty($video->processed_movie)
        ) {
            throw new \RuntimeException(
                'YouTube工程へ進めるための条件を満たしていません。'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | 管理者承認
        |--------------------------------------------------------------------------
        */

        $video->update([
            'review_status' => ProductVideoDraft::REVIEW_APPROVED,
            'reviewed_at' => now(),
            'reviewed_by' => $userId,
            'workflow_stage' => ProductVideoDraft::STAGE_YOUTUBE,
        ]);

        Audit::log(
            action: 'product_video_review_approved',
            target: $video,
            before: $before,
            after: $video->fresh()->toArray(),
            description: '商品SNS動画の管理者審査承認'
        );

        /*
        |--------------------------------------------------------------------------
        | YouTubeアップロードJob
        |--------------------------------------------------------------------------
        */

        UploadProductVideoToYoutubeJob::dispatch(
            $video->id
        );
    }

    public function startYoutubeUpload(
        ProductVideoDraft $video
    ): void {

        if (
            $video->youtube_status
                === ProductVideoDraft::YOUTUBE_PRIVATE
            && !empty($video->youtube_video_id)
        ) {
            return;
        }

        if (
            $video->youtube_status
                === ProductVideoDraft::YOUTUBE_PUBLIC
            && !empty($video->youtube_video_id)
        ) {
            return;
        }

        $before = $video->toArray();

        $video->update([
            'workflow_stage'
                => ProductVideoDraft::STAGE_YOUTUBE,

            'youtube_status'
                => ProductVideoDraft::YOUTUBE_UPLOADING,
        ]);

        Audit::log(
            action: 'product_video_youtube_started',
            target: $video,
            before: $before,
            after: $video->fresh()->toArray(),
            description: '商品SNS動画YouTubeアップロード開始'
        );
    }


    public function completeYoutubeUpload(
        ProductVideoDraft $video,
        array $result
    ): void {

        if (empty($result['video_id'])) {
            throw new \RuntimeException(
                'YouTube動画IDが取得できませんでした。'
            );
        }

        $before = $video->toArray();

        $video->update([
            'youtube_status'
                => ProductVideoDraft::YOUTUBE_PRIVATE,

            'youtube_video_id'
                => $result['video_id'],

            'youtube_uploaded_at'
                => now(),
        ]);

        Audit::log(
            action: 'product_video_youtube_completed',
            target: $video,
            before: $before,
            after: $video->fresh()->toArray(),
            description: '商品SNS動画YouTubeアップロード完了'
        );
    }


    public function failYoutubeUpload(
        ProductVideoDraft $video,
        ?string $reason = null
    ): void {

        $before = $video->toArray();

        $video->update([
            'youtube_status'
                => ProductVideoDraft::YOUTUBE_FAILED,
        ]);

        Audit::log(
            action: 'product_video_youtube_failed',
            target: $video,
            before: $before,
            after: $video->fresh()->toArray(),
            description:
                '商品SNS動画YouTubeアップロード失敗'
                . ($reason ? ': ' . $reason : ''),
        );
    }

    /**
     * SNS配信開始
     */
    public function startSocialDistribution(
        ProductVideoDraft $video
    ): void {

        /*
        |--------------------------------------------------------------------------
        | SNS配信開始条件
        |--------------------------------------------------------------------------
        */

        if (
            $video->review_status
                !== ProductVideoDraft::REVIEW_APPROVED
            ||
            !in_array(
                $video->youtube_status,
                [
                    ProductVideoDraft::YOUTUBE_PRIVATE,
                    ProductVideoDraft::YOUTUBE_PUBLIC,
                ],
                true
            )
            ||
            empty($video->youtube_video_id)
        ) {
            throw new \RuntimeException(
                'SNS配信を開始するための条件を満たしていません。'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | 既存SNSレコード確認
        |--------------------------------------------------------------------------
        */

        $platforms = [
            ProductVideoSocialPost::PLATFORM_INSTAGRAM,
            ProductVideoSocialPost::PLATFORM_TIKTOK,
            ProductVideoSocialPost::PLATFORM_FACEBOOK,
            ProductVideoSocialPost::PLATFORM_X,
        ];

        /*
        |--------------------------------------------------------------------------
        | SNS配信レコード作成
        |--------------------------------------------------------------------------
        */

        foreach ($platforms as $platform) {

            ProductVideoSocialPost::firstOrCreate(
                [
                    'product_video_draft_id' => $video->id,
                    'platform' => $platform,
                ],
                [
                    'status'
                        => ProductVideoSocialPost::STATUS_PENDING,

                    'visibility'
                        => ProductVideoSocialPost::VISIBILITY_PRIVATE,
                ]
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Audit
        |--------------------------------------------------------------------------
        */

        Audit::log(
            action:
                'product_video_social_distribution_started',

            target:
                $video,

            before:
                $video->toArray(),

            after:
                $video->fresh()->toArray(),

            description:
                '商品SNS動画SNS配信開始'
        );
    }


}