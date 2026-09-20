<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProductVideoDraft;
use App\Services\Video\Workflow\ProductVideoWorkflowService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

use App\Jobs\UploadProductVideoToYoutubeJob;
use Throwable;

class ProductVideoReviewController extends Controller
{

    public function review(
        ProductVideoDraft $video,
        \App\Services\Video\Storage\VideoStorageService $storage
    ) {
        $previewUrl = null;

        if (
            $video->process_status
                === ProductVideoDraft::PROCESS_COMPLETED &&
            !empty($video->processed_movie) &&
            $storage->exists($video->processed_movie)
        ) {
            $previewUrl = $storage->temporaryUrl(
                $video->processed_movie,
                30
            );
        }

        return view(
            'admin.product_videos.review',
            compact(
                'video',
                'previewUrl'
            )
        );
    }

    public function approve(
        ProductVideoDraft $video,
        ProductVideoWorkflowService $workflow
    ): RedirectResponse {

        try {

            $workflow->approveReview(
                $video,
                auth()->id()
            );

            return redirect()
                ->back()
                ->with(
                    'success',
                    '商品動画を承認しました。YouTube工程へ移行します。'
                );

        } catch (Throwable $e) {

            return redirect()
                ->back()
                ->with(
                    'error',
                    $e->getMessage()
                );
        }
    }

    public function reject(
        Request $request,
        ProductVideoDraft $video,
        \App\Services\Video\Storage\VideoStorageService $storage
    ): RedirectResponse {

        try {

            $request->validate([
                'review_reject_reason' => [
                    'required',
                    'string',
                    'in:商品と無関係,不適切な内容,権利侵害の疑い,虚偽・誤認を招く内容,品質上の問題,その他',
                ],
            ]);

            if (
                $video->review_status
                    !== ProductVideoDraft::REVIEW_PENDING
            ) {
                throw new \RuntimeException(
                    'この商品動画は現在、掲載不可にできる状態ではありません。'
                );
            }

            /*
             * S3ファイル削除
             */
            $files = [
                $video->processed_movie,
                $video->preview_movie,
                $video->thumbnail,
            ];

            foreach ($files as $path) {

                if (
                    !empty($path) &&
                    $storage->exists($path)
                ) {
                    $deleted = $storage->delete($path);

                    if (!$deleted) {
                        throw new \RuntimeException(
                            '動画ファイルの削除に失敗しました: '
                            . $path
                        );
                    }

                    logger()->info(
                        'Product video reject: S3 file deleted',
                        [
                            'video_id' => $video->id,
                            'path' => $path,
                            'exists_after_delete' => $storage->exists($path),
                        ]
                    );
                }
            }

            /*
             * DBを掲載不可へ変更
             */
            $video->update([
                'review_status' => ProductVideoDraft::REVIEW_REJECTED,
                'review_reject_reason' => $request->input(
                    'review_reject_reason'
                ),
                'reviewed_at' => now(),
                'reviewed_by' => auth()->id(),
                'is_active' => false,
            ]);

            return redirect()
                ->back()
                ->with(
                    'success',
                    '商品動画を掲載不可にしました。'
                );

        } catch (Throwable $e) {

            return redirect()
                ->back()
                ->with(
                    'error',
                    $e->getMessage()
                );
        }
    }

    public function retryYoutube(
        ProductVideoDraft $video
    ): RedirectResponse {

        try {

            if (
                $video->review_status
                    !== ProductVideoDraft::REVIEW_APPROVED
            ) {
                throw new \RuntimeException(
                    '管理者審査が承認されていないため、YouTube再アップロードできません。'
                );
            }

            if (
                $video->youtube_status
                    !== ProductVideoDraft::YOUTUBE_FAILED
            ) {
                throw new \RuntimeException(
                    'YouTubeアップロード失敗状態ではありません。'
                );
            }

            UploadProductVideoToYoutubeJob::dispatch(
                $video->id
            );

            return redirect()
                ->back()
                ->with(
                    'success',
                    'YouTube再アップロードを開始しました。'
                );

        } catch (Throwable $e) {

            return redirect()
                ->back()
                ->with(
                    'error',
                    $e->getMessage()
                );
        }
    }




}