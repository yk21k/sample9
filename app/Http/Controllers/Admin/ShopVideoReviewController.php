<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ShopVideoDraft;
use App\Helpers\Audit;
use App\Jobs\UploadShopVideoToYoutubeJob;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ShopVideoReviewController extends Controller
{
    /**
     * 審査待ち一覧
     */
    public function index()
    {
        $videos = ShopVideoDraft::query()
            ->with('shop')
            ->where(
                'review_status',
                ShopVideoDraft::REVIEW_PENDING
            )
            ->where(
                'process_status',
                ShopVideoDraft::PROCESS_COMPLETED
            )
            ->latest()
            ->paginate(20);

        foreach ($videos as $video) {

            $video->thumbnail_url = null;

            if ($video->thumbnail) {
                $video->thumbnail_url =
                    Storage::disk('s3')->temporaryUrl(
                        $video->thumbnail,
                        now()->addMinutes(10)
                    );
            }
        }

        return view(
            'admin.shop_video_reviews.index',
            compact('videos')
        );
    }

    /**
     * 詳細・管理者審査画面
     */
    public function show(
        ShopVideoDraft $video
    ) {
        abort_unless(
            $video->review_status
                === ShopVideoDraft::REVIEW_PENDING
            &&
            $video->process_status
                === ShopVideoDraft::PROCESS_COMPLETED,
            404
        );

        $video->load('shop');

        $video->preview_url = null;
        $video->processed_url = null;
        $video->thumbnail_url = null;

        if ($video->preview_movie) {
            $video->preview_url =
                Storage::disk('s3')->temporaryUrl(
                    $video->preview_movie,
                    now()->addMinutes(30)
                );
        }

        if ($video->processed_movie) {
            $video->processed_url =
                Storage::disk('s3')->temporaryUrl(
                    $video->processed_movie,
                    now()->addMinutes(30)
                );
        }

        if ($video->thumbnail) {
            $video->thumbnail_url =
                Storage::disk('s3')->temporaryUrl(
                    $video->thumbnail,
                    now()->addMinutes(30)
                );
        }

        return view(
            'admin.shop_video_reviews.show',
            compact('video')
        );
    }

    /**
     * 管理者承認
     */
    public function approve(
        ShopVideoDraft $video
    ): RedirectResponse {

        $jobVideoId = null;

        DB::transaction(function () use (
            $video,
            &$jobVideoId
        ) {

            $video = ShopVideoDraft::query()
                ->whereKey($video->id)
                ->lockForUpdate()
                ->firstOrFail();

            /*
            |--------------------------------------------------------------------------
            | 承認可能状態確認
            |--------------------------------------------------------------------------
            */

            abort_unless(
                $video->review_status
                    === ShopVideoDraft::REVIEW_PENDING
                &&
                $video->process_status
                    === ShopVideoDraft::PROCESS_COMPLETED,
                404
            );

            /*
            |--------------------------------------------------------------------------
            | YouTube未処理確認
            |--------------------------------------------------------------------------
            */

            abort_unless(
                $video->youtube_status
                    === ShopVideoDraft::YOUTUBE_NONE
                &&
                empty($video->youtube_video_id),
                404
            );

            /*
            |--------------------------------------------------------------------------
            | Before
            |--------------------------------------------------------------------------
            */

            $before = $video->toArray();

            /*
            |--------------------------------------------------------------------------
            | 管理者承認
            |--------------------------------------------------------------------------
            */

            $video->update([

                'review_status'
                    => ShopVideoDraft::REVIEW_APPROVED,

                'workflow_stage'
                    => ShopVideoDraft::STAGE_YOUTUBE,

                'publish_status'
                    => ShopVideoDraft::PUBLISH_DRAFT,

                'reviewed_at'
                    => now(),

                'reviewed_by'
                    => auth()->id(),

            ]);

            /*
            |--------------------------------------------------------------------------
            | Audit
            |--------------------------------------------------------------------------
            */

            Audit::log(

                action:
                    ShopVideoDraft::ACTION_REVIEW_APPROVED,

                target: $video,

                before: $before,

                after: $video->fresh()->toArray(),

                description:
                    'ショップ動画管理者審査承認'

            );

            $jobVideoId = $video->id;
        });

        /*
        |--------------------------------------------------------------------------
        | Transaction完了後にYouTube Job
        |--------------------------------------------------------------------------
        */

        UploadShopVideoToYoutubeJob::dispatch(
            $jobVideoId
        );

        return redirect()
            ->route(
                'admin.shop-video-reviews.index'
            )
            ->with(
                'success',
                '動画を承認しました。YouTubeへのアップロードを開始しました。'
            );
    }

    /**
     * 管理者却下
     */
    public function reject(
        ShopVideoDraft $video
    ): RedirectResponse {

        DB::transaction(function () use ($video) {

            $video = ShopVideoDraft::query()
                ->whereKey($video->id)
                ->lockForUpdate()
                ->firstOrFail();

            /*
            |--------------------------------------------------------------------------
            | 却下可能状態確認
            |--------------------------------------------------------------------------
            */

            abort_unless(
                $video->review_status
                    === ShopVideoDraft::REVIEW_PENDING
                &&
                $video->process_status
                    === ShopVideoDraft::PROCESS_COMPLETED,
                404
            );

            /*
            |--------------------------------------------------------------------------
            | Before
            |--------------------------------------------------------------------------
            */

            $before = $video->toArray();

            /*
            |--------------------------------------------------------------------------
            | 却下
            |--------------------------------------------------------------------------
            */

            $video->update([

                'review_status'
                    => ShopVideoDraft::REVIEW_REJECTED,

                'workflow_stage'
                    => ShopVideoDraft::STAGE_REVIEW,

                'publish_status'
                    => ShopVideoDraft::PUBLISH_DRAFT,

                'reviewed_at'
                    => now(),

                'reviewed_by'
                    => auth()->id(),

            ]);

            /*
            |--------------------------------------------------------------------------
            | Audit
            |--------------------------------------------------------------------------
            */

            Audit::log(

                action:
                    ShopVideoDraft::ACTION_REVIEW_REJECTED,

                target: $video,

                before: $before,

                after: $video->fresh()->toArray(),

                description:
                    'ショップ動画管理者審査却下'

            );
        });

        return redirect()
            ->route(
                'admin.shop-video-reviews.index'
            )
            ->with(
                'success',
                '動画を却下しました。'
            );
    }
}