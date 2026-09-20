<?php

namespace App\Services\Video\Workflow;

use App\Models\ShopVideoDraft;
use App\Helpers\Audit;
use App\Services\Video\Preview\VideoPreviewService;
use App\Jobs\GenerateShopVideoPreviewJob;
use App\Jobs\AnalyzeShopVideoJob;
use App\Services\Video\Ai\VideoAiService;
use App\Jobs\CheckShopVideoAiJob;

class ShopVideoWorkflowService extends VideoWorkflowService
{
    /**
     * アップロード完了
     */
    public function uploadCompleted(
        ShopVideoDraft $video
    )
    {
        $editMode = ShopVideoDraft::EDIT_MODE_PENDING;

        \Log::info(
            'ShopVideo uploadCompleted EDIT MODE',
            [
                'video_id'
                    => $video->id,

                'edit_mode_constant'
                    => $editMode,
            ]
        );

        $before = $video->toArray();

        $video->update([

            'process_status'
                => ShopVideoDraft::PROCESS_WAITING,

            'preview_status'
                => ShopVideoDraft::PREVIEW_WAITING,

            'edit_mode'
                => $editMode,

        ]);

        $video->refresh();

        \Log::info(
            'ShopVideo uploadCompleted EDIT MODE SAVED',
            [
                'video_id'
                    => $video->id,

                'edit_mode'
                    => $video->edit_mode,

                'process_status'
                    => $video->process_status,

                'preview_status'
                    => $video->preview_status,
            ]
        );

        Audit::log(

            action:
                ShopVideoDraft::ACTION_UPLOAD_COMPLETED,

            target:
                $video,

            before:
                $before,

            after:
                $video->toArray(),

            description:
                'ショップ動画アップロード完了'

        );

        GenerateShopVideoPreviewJob::dispatch(
            $video
        );
    }

    public function startAi(
        ShopVideoDraft $video,
        VideoAiService $videoAi
    ): void {

        $before = $video->toArray();

        $result = $videoAi->start(
            $video
        );

        $video->update([

            'workflow_stage'
                => ShopVideoDraft::STAGE_AI,

            'ai_status'
                => ShopVideoDraft::AI_PROCESSING,

            'ai_provider'
                => $result['provider'],

            'rekognition_job_id'
                => $result['job_id'],

        ]);

        Audit::log(

            action: ShopVideoDraft::ACTION_AI_STARTED,

            target: $video,

            before: $before,

            after: $video->fresh()->toArray(),

            description: '動画AI開始'

        );

        /*
        |--------------------------------------------------------------------------
        | Rekognition結果確認ジョブ
        |--------------------------------------------------------------------------
        */

        CheckShopVideoAiJob::dispatch(
            $video->id
        );
    }

    public function completeAi(
        ShopVideoDraft $video,
        array $result
    )
    {
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


        switch ($video->ai_status) {


            case ShopVideoDraft::AI_APPROVED:
                $video->update([
                    'workflow_stage'
                        => ShopVideoDraft::STAGE_PROCESS,
                    'process_status'
                        => ShopVideoDraft::PROCESS_WAITING,
                ]);

                break;



            case ShopVideoDraft::AI_WARNING:


            case ShopVideoDraft::AI_REJECTED:


                $video->update([

                    'workflow_stage'
                        => ShopVideoDraft::STAGE_REVIEW,

                ]);

                break;

        }

        Audit::log(

            action: ShopVideoDraft::ACTION_AI_COMPLETED,

            target: $video,

            before: $before,

            after: $video->fresh()->toArray(),

            description: '動画AI完了'

        );

        /*
        |--------------------------------------------------------------------------
        | 次工程（AI）
        |--------------------------------------------------------------------------
        */

        // AnalyzeShopVideoJob::dispatch($video);

    }

    public function failAi(
        ShopVideoDraft $video,
        ?string $reason = null
    ): void {

        $before = $video->toArray();

        $video->update([

            'ai_status'
                => ShopVideoDraft::AI_FAILED,

        ]);

        Audit::log(

            action: ShopVideoDraft::ACTION_AI_FAILED,

            target: $video,

            before: $before,

            after: $video->fresh()->toArray(),

            description: '動画AI失敗'
                . ($reason ? ': ' . $reason : '')

        );
    }

    /*
    |--------------------------------------------------------------------------
    | Processing
    |--------------------------------------------------------------------------
    */

    public function startProcessing(
        ShopVideoDraft $video
    ): void {

        $before = $video->toArray();

        $video->update([

            'workflow_stage'
                => ShopVideoDraft::STAGE_PROCESS,

        ]);

        Audit::log(

            action:
                ShopVideoDraft::ACTION_PROCESS_STARTED,

            target:
                $video,

            before:
                $before,

            after:
                $video->fresh()->toArray(),

            description:
                '動画加工開始'

        );
    }

    public function completeProcessing(
        ShopVideoDraft $video,
        array $result
    ): void {

        $before = $video->toArray();

        $video->update([

            'processed_movie'
                => $result['processed_movie'],

            'duration'
                => $result['duration'],

            'file_size'
                => $result['file_size'],

            'processed_at'
                => now(),

            'process_status'
                => ShopVideoDraft::PROCESS_COMPLETED,

            'workflow_stage'
                => ShopVideoDraft::STAGE_SELLER_REVIEW,

            'seller_review_status'
                => ShopVideoDraft::SELLER_REVIEW_PENDING,

        ]);

        Audit::log(

            action:
                ShopVideoDraft::ACTION_PROCESS_COMPLETED,

            target:
                $video,

            before:
                $before,

            after:
                $video->fresh()->toArray(),

            description:
                '動画加工完了・出品者確認待ち'

        );
    }

    public function failProcessing(
        ShopVideoDraft $video,
        ?string $reason = null
    )
    {
        $before = $video->toArray();

        $video->update([

            'process_status'
                => ShopVideoDraft::PROCESS_FAILED,

        ]);

        Audit::log(
            action: ShopVideoDraft::ACTION_PROCESS_FAILED,
            target: $video,
            before: $before,
            after: $video->fresh()->toArray(),
            description: '動画加工失敗'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | YouTube
    |--------------------------------------------------------------------------
    */

    public function startYoutubeUpload(
        ShopVideoDraft $video
    ): void {

        /*
        |--------------------------------------------------------------------------
        | 既にアップロード済みなら何もしない
        |--------------------------------------------------------------------------
        */

        if (
            $video->youtube_status
                === ShopVideoDraft::YOUTUBE_PRIVATE
            && !empty($video->youtube_video_id)
        ) {

            return;
        }

        if (
            $video->youtube_status
                === ShopVideoDraft::YOUTUBE_PUBLIC
            && !empty($video->youtube_video_id)
        ) {

            return;
        }

        $before = $video->toArray();

        $video->update([

            'workflow_stage'
                => ShopVideoDraft::STAGE_YOUTUBE,

            'youtube_status'
                => ShopVideoDraft::YOUTUBE_UPLOADING,

        ]);

        Audit::log(

            action: 'shop_video_youtube_started',

            target: $video,

            before: $before,

            after: $video->fresh()->toArray(),

            description: 'YouTubeアップロード開始'

        );
    }


    public function completeYoutubeUpload(
        ShopVideoDraft $video,
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
                => ShopVideoDraft::YOUTUBE_PRIVATE,

            'youtube_video_id'
                => $result['video_id'],

            'youtube_uploaded_at'
                => now(),

        ]);

        Audit::log(

            action: 'shop_video_youtube_completed',

            target: $video,

            before: $before,

            after: $video->fresh()->toArray(),

            description: 'YouTubeアップロード完了'

        );
    }


    public function failYoutubeUpload(
        ShopVideoDraft $video,
        ?string $reason = null
    ): void {

        $before = $video->toArray();

        $video->update([

            'youtube_status'
                => ShopVideoDraft::YOUTUBE_FAILED,

        ]);

        Audit::log(

            action: 'shop_video_youtube_failed',

            target: $video,

            before: $before,

            after: $video->fresh()->toArray(),

            description: 'YouTubeアップロード失敗'
                . ($reason ? ': ' . $reason : '')

        );
    }

    /*
    |--------------------------------------------------------------------------
    | Preview
    |--------------------------------------------------------------------------
    */

    public function startPreview(
        ShopVideoDraft $video
    ): void {

        $before = $video->toArray();

        $video->update([

            'workflow_stage'
                => ShopVideoDraft::STAGE_PREVIEW,

            'preview_status'
                => ShopVideoDraft::PREVIEW_GENERATING,

        ]);

        Audit::log(

            action: ShopVideoDraft::ACTION_PREVIEW_STARTED,

            target: $video,

            before: $before,

            after: $video->fresh()->toArray(),

            description: '動画Preview生成開始'

        );


        try {

            $result = app(
                VideoPreviewService::class
            )->generate(
                $video->fresh()
            );


            $this->completePreview(
                $video,
                $result
            );


        } catch (\Throwable $e) {


            $this->failPreview(
                $video,
                $e->getMessage()
            );


            throw $e;

        }

    }

    public function completePreview(
        ShopVideoDraft $video,
        array $result
    ): void {
        $before = $video->toArray();

        $video->update([

            'preview_movie'
                => $result['preview_movie'],

            'thumbnail'
                => $result['thumbnail'],

            'preview_status'
                => ShopVideoDraft::PREVIEW_COMPLETED,

            'preview_generated_at'
                => now(),

        ]);

        Audit::log(

            action: ShopVideoDraft::ACTION_PREVIEW_COMPLETED,

            target: $video,

            before: $before,

            after: $video->fresh()->toArray(),

            description: '動画Preview生成完了'

        );

        AnalyzeShopVideoJob::dispatch(
            $video->id
        );
    }

    public function failPreview(
        ShopVideoDraft $video,
        ?string $reason = null
    ): void 
    {
        $before = $video->toArray();

        $video->update([

            'preview_status'
                => ShopVideoDraft::PREVIEW_FAILED,

        ]);

        Audit::log(

            action: ShopVideoDraft::ACTION_PREVIEW_FAILED,

            target: $video,

            before: $before,

            after: $video->fresh()->toArray(),

            description: '動画Preview生成失敗'
                . ($reason ? ': ' . $reason : '')

        );
    }

    /*
    |--------------------------------------------------------------------------
    | Seller Review
    |--------------------------------------------------------------------------
    */

    /**
     * 出品者による加工後動画確認・承認
     *
     * V1 / V2 / V3 のいずれかで動画編集が完了した後、
     * 出品者が加工後動画を確認し、問題なければ承認する。
     *
     * 承認後は運営者審査へ移行する。
     */
    public function approveSellerReview(
        ShopVideoDraft $video
    ): void {

        /*
        |--------------------------------------------------------------------------
        | 出品者確認待ちであることを確認
        |--------------------------------------------------------------------------
        */

        if (
            $video->seller_review_status
            !== ShopVideoDraft::SELLER_REVIEW_PENDING
        ) {

            throw new \RuntimeException(
                'この動画は出品者確認待ちではありません。'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | 対象動画が現在の店舗に属していることを確認
        |--------------------------------------------------------------------------
        */

        $shop = auth()->user()->currentShop();

        if (
            !$shop ||
            $video->shop_id !== $shop->id
        ) {

            abort(403);
        }


        /*
        |--------------------------------------------------------------------------
        | 加工完了確認
        |--------------------------------------------------------------------------
        */

        if (
            $video->process_status
            !== ShopVideoDraft::PROCESS_COMPLETED
        ) {

            throw new \RuntimeException(
                '動画加工が完了していません。'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | 加工後動画存在確認
        |--------------------------------------------------------------------------
        */

        if (
            empty($video->processed_movie)
        ) {

            throw new \RuntimeException(
                '加工後動画が存在しません。'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | 編集モード確認
        |--------------------------------------------------------------------------
        */

        if (
            !in_array(
                $video->edit_mode,
                [
                    ShopVideoDraft::EDIT_MODE_V1,
                    ShopVideoDraft::EDIT_MODE_V2,
                    ShopVideoDraft::EDIT_MODE_V3,
                ],
                true
            )
        ) {

            throw new \RuntimeException(
                '動画編集モードが不正です。'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | 承認前状態
        |--------------------------------------------------------------------------
        */

        $before = $video->toArray();


        /*
        |--------------------------------------------------------------------------
        | 出品者承認
        |--------------------------------------------------------------------------
        */

        $video->update([

            'seller_review_status'
                => ShopVideoDraft::SELLER_REVIEW_APPROVED,

            'seller_reviewed_at'
                => now(),

            'seller_reviewed_by'
                => auth()->id(),

            /*
            |--------------------------------------------------------------------------
            | 運営者審査へ
            |--------------------------------------------------------------------------
            */

            'workflow_stage'
                => ShopVideoDraft::STAGE_REVIEW,

            'review_status'
                => ShopVideoDraft::REVIEW_PENDING,

        ]);


        /*
        |--------------------------------------------------------------------------
        | Audit
        |--------------------------------------------------------------------------
        */

        Audit::log(

            action:
                ShopVideoDraft::ACTION_SELLER_REVIEW_APPROVED,

            target:
                $video,

            before:
                $before,

            after:
                $video->fresh()->toArray(),

            description:
                '出品者による加工後動画承認・運営者審査待ち'

        );
    }




}