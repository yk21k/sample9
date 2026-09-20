<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ShopVideoDraft;
use App\Services\Video\Preview\VideoPreviewService;
use Illuminate\Support\Facades\Storage;
use App\Services\Video\Workflow\ShopVideoWorkflowService;
use App\Models\ShopVideoBgm;
use App\Services\Video\Usage\ShopVideoUsageService;

class ShopVideoController extends Controller
{

    public function index(
        ShopVideoUsageService $usageService
    ) {
        /*
        |--------------------------------------------------------------------------
        | 現在の店舗
        |--------------------------------------------------------------------------
        */

        $shop = auth()->user()->currentShop();

        /*
        |--------------------------------------------------------------------------
        | 動画一覧
        |--------------------------------------------------------------------------
        */

        $videos = ShopVideoDraft::query()
            ->where(
                'shop_id',
                $shop->id
            )
            ->latest()
            ->paginate(12);

        /*
        |--------------------------------------------------------------------------
        | 動画ステータス集計
        |--------------------------------------------------------------------------
        */

        $videoQuery = ShopVideoDraft::query()
            ->where(
                'shop_id',
                $shop->id
            );

        $summary = [

            /*
            |--------------------------------------------------------------------------
            | 登録動画数
            |--------------------------------------------------------------------------
            */

            'total' => (clone $videoQuery)
                ->count(),

            /*
            |--------------------------------------------------------------------------
            | AI審査待ち
            |--------------------------------------------------------------------------
            */

            'ai_pending' => (clone $videoQuery)
                ->where(
                    'ai_status',
                    ShopVideoDraft::AI_PENDING
                )
                ->count(),

            /*
            |--------------------------------------------------------------------------
            | 動画加工中
            |--------------------------------------------------------------------------
            */

            'processing' => (clone $videoQuery)
                ->where(
                    'process_status',
                    ShopVideoDraft::PROCESS_RUNNING
                )
                ->count(),

            /*
            |--------------------------------------------------------------------------
            | 公開済み
            |--------------------------------------------------------------------------
            */

            'published' => (clone $videoQuery)
                ->where(
                    'publish_status',
                    ShopVideoDraft::PUBLISH_PUBLISHED
                )
                ->count(),

            /*
            |--------------------------------------------------------------------------
            | AI警告
            |--------------------------------------------------------------------------
            */

            'warning' => (clone $videoQuery)
                ->where(
                    'ai_status',
                    ShopVideoDraft::AI_WARNING
                )
                ->count(),

        ];

        /*
        |--------------------------------------------------------------------------
        | 本日の動画加工利用状況
        |--------------------------------------------------------------------------
        */

        $usage = [

            'limit'
                => $usageService->getDailyLimit(
                    $shop
                ),

            'used'
                => $usageService->getTodayUsage(
                    $shop
                ),

            'remaining'
                => $usageService->getRemaining(
                    $shop
                ),

        ];

        /*
        |--------------------------------------------------------------------------
        | View
        |--------------------------------------------------------------------------
        */

        return view(
            'sellers.shop_videos.index',
            compact(
                'videos',
                'summary',
                'usage'
            )
        );
    }

    public function create()
    {
        return view(
            'sellers.shop_videos.create'
        );
    }

    public function store(
        Request $request
    ) {

        $shop = auth()->user()->currentShop();


        /*
        |--------------------------------------------------------------------------
        | 1店舗1動画
        |--------------------------------------------------------------------------
        */

        if (
            ShopVideoDraft::query()
                ->where(
                    'shop_id',
                    $shop->id
                )
                ->exists()
        ) {

            return back()->with(
                'error',
                'すでに店舗紹介動画が登録されています。既存の動画を削除してから新しい動画を登録してください。'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Validation
        |--------------------------------------------------------------------------
        */

        $validated = $request->validate([

            'title' => 'required|max:255',

            'description' => 'nullable',

            'movie' => [
                'required',
                'file',
                'mimes:mp4,mov,webm',
                'max:204800',
            ],

        ]);


        /*
        |--------------------------------------------------------------------------
        | S3保存
        |--------------------------------------------------------------------------
        */

        $path = $request
            ->file('movie')
            ->store(
                'shop-videos/original',
                's3'
            );


        /*
        |--------------------------------------------------------------------------
        | Draft作成
        |--------------------------------------------------------------------------
        */

        $video = ShopVideoDraft::create([

            'shop_id'
                => $shop->id,

            'created_by'
                => auth()->id(),

            'updated_by'
                => auth()->id(),

            'title'
                => $validated['title'],

            'description'
                => $validated['description'] ?? null,

            'original_movie'
                => $path,

            'file_size'
                => $request
                    ->file('movie')
                    ->getSize(),

        ]);


        /*
        |--------------------------------------------------------------------------
        | Workflow開始
        |--------------------------------------------------------------------------
        */

        app(
            ShopVideoWorkflowService::class
        )->uploadCompleted(
            $video
        );


        return redirect()
            ->route(
                'seller.shop-videos.index'
            )
            ->with(
                'success',
                '動画を登録しました'
            );
    }

    public function show(
        ShopVideoDraft $shopVideo
    ) {
        $shop = auth()->user()->currentShop();

        // dd([ 'video_id' => $shopVideo->id, 'video_shop_id' => $shopVideo->shop_id, 'current_shop_id' => $shop?->id, 'user_id' => auth()->id(), ]);

        abort_unless(
            $shopVideo->shop_id === $shop->id,
            403
        );

        return view(
            'sellers.shop_videos.show',
            [ 'video' => $shopVideo, ]
        );
    }

    public function edit(
        ShopVideoDraft $shopVideo
    ) {
        $shop = auth()->user()->currentShop();

        abort_unless(
            $shopVideo->shop_id === $shop->id,
            403
        );

        abort_unless(
            $shopVideo->preview_status
                === ShopVideoDraft::PREVIEW_COMPLETED,
            403
        );

        $bgms = \App\Models\ShopVideoBgm::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | BGM試聴用URL
        |--------------------------------------------------------------------------
        */

        $bgmUrls = [];

        foreach ($bgms as $bgm) {

            if (
                $bgm->file_path
                &&
                \Storage::disk('s3')->exists(
                    $bgm->file_path
                )
            ) {

                $bgmUrls[$bgm->id] =
                    \Storage::disk('s3')->temporaryUrl(
                        $bgm->file_path,
                        now()->addMinutes(30)
                    );

            }

        }

        return view(
            'sellers.shop_videos.edit',
            [
                'video' => $shopVideo,
                'bgms' => $bgms,
                'bgmUrls' => $bgmUrls,
            ]
        );
    }

    public function update(
        Request $request,
        ShopVideoDraft $shopVideo
    ) {

        /*
        |--------------------------------------------------------------------------
        | すでに加工中なら起動不可
        |--------------------------------------------------------------------------
        */

        if (
            $shopVideo->process_status
            === ShopVideoDraft::PROCESS_RUNNING
        ) {
            return back()->with(
                'error',
                '現在、動画を加工中です。'
            );
        }

        $shop = auth()->user()->currentShop();

        abort_unless(
            $shopVideo->shop_id === $shop->id,
            403
        );

        /*
        |--------------------------------------------------------------------------
        | Preview完成後のみ編集設定を変更可能
        |--------------------------------------------------------------------------
        */

        abort_unless(
            $shopVideo->preview_status
                === ShopVideoDraft::PREVIEW_COMPLETED,
            403
        );


        /*
        |--------------------------------------------------------------------------
        | Validation
        |--------------------------------------------------------------------------
        */

        $validated = $request->validate([

            'edit_mode' => [
                'required',
                'in:v1,v2,v3',
            ],

            'bgm_id' => [
                'nullable',
                'integer',
            ],

        ]);

        \Log::info('ShopVideo update REQUEST', [
            'video_id' => $shopVideo->id,
            'all' => $request->all(),
        ]);


        /*
        |--------------------------------------------------------------------------
        | V1の場合はBGMを解除
        |--------------------------------------------------------------------------
        */

        if (
            $validated['edit_mode']
            === ShopVideoDraft::EDIT_MODE_V1
        ) {

            $validated['bgm_id'] = null;

        }


        /*
        |--------------------------------------------------------------------------
        | 保存
        |--------------------------------------------------------------------------
        */

        \Log::info('ShopVideo edit settings SAVE', [
            'video_id' => $shopVideo->id,
            'validated_edit_mode' => $validated['edit_mode'],
            'validated_bgm_id' => $validated['bgm_id'] ?? null,
        ]);

        $shopVideo->update([

            'edit_mode'
                => $validated['edit_mode'],

            'bgm_id'
                => $validated['bgm_id'] ?? null,

            'updated_by'
                => auth()->id(),

        ]);

        $shopVideo->refresh();

        \Log::info('ShopVideo edit settings SAVED', [
            'video_id' => $shopVideo->id,
            'edit_mode' => $shopVideo->edit_mode,
            'bgm_id' => $shopVideo->bgm_id,
        ]);


        return redirect()

            ->route(
                'seller.shop-videos.edit',
                $shopVideo
            )

            ->with(
                'success',
                '動画編集設定を保存しました。'
            );
    }

    public function process(
        ShopVideoDraft $shopVideo,
        ShopVideoUsageService $usageService
    ) {
        $shop = auth()->user()->currentShop();

        /*
        |--------------------------------------------------------------------------
        | 店舗チェック
        |--------------------------------------------------------------------------
        */

        abort_unless(
            $shopVideo->shop_id === $shop->id,
            403
        );

        /*
        |--------------------------------------------------------------------------
        | すでに加工中なら起動不可
        |--------------------------------------------------------------------------
        */

        if (
            $shopVideo->process_status
            === ShopVideoDraft::PROCESS_RUNNING
        ) {
            return back()->with(
                'error',
                '現在、動画を加工中です。'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Preview完成後のみ加工可能
        |--------------------------------------------------------------------------
        */

        abort_unless(
            $shopVideo->preview_status
            === ShopVideoDraft::PREVIEW_COMPLETED,
            403
        );

        /*
        |--------------------------------------------------------------------------
        | 編集モード確認
        |--------------------------------------------------------------------------
        */

        abort_unless(
            in_array(
                $shopVideo->edit_mode,
                [
                    ShopVideoDraft::EDIT_MODE_V1,
                    ShopVideoDraft::EDIT_MODE_V2,
                    ShopVideoDraft::EDIT_MODE_V3,
                ],
                true
            ),
            403
        );

        /*
        |--------------------------------------------------------------------------
        | V2 / V3 はBGM必須
        |--------------------------------------------------------------------------
        */

        if (
            in_array(
                $shopVideo->edit_mode,
                [
                    ShopVideoDraft::EDIT_MODE_V2,
                    ShopVideoDraft::EDIT_MODE_V3,
                ],
                true
            )
            &&
            !$shopVideo->bgm_id
        ) {
            return back()->with(
                'error',
                'V2・V3ではBGMを選択してください。'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | 利用回数の事前確認
        |--------------------------------------------------------------------------
        |
        | UI上の事前チェック。
        | 同時実行などの競合はJob側で再チェックする。
        | ここでは利用回数を消費しない。
        |
        */

        if (
            !$usageService->canStartProcessing($shop)
        ) {
            return back()->with(
                'error',
                '本日の動画加工利用回数の上限に達しています。'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Job起動
        |--------------------------------------------------------------------------
        */

        \App\Jobs\ProcessShopVideoJob::dispatch(
            $shopVideo->id
        );

        \Log::info(
            'ShopVideo process DISPATCHED',
            [
                'video_id'
                    => $shopVideo->id,

                'shop_id'
                    => $shop->id,
            ]
        );

        return redirect()
            ->route(
                'seller.shop-videos.edit',
                $shopVideo
            )
            ->with(
                'success',
                '動画の加工を受け付けました。'
            );
    }

    public function approveSellerReview(
        ShopVideoDraft $shopVideo,
        ShopVideoWorkflowService $workflow
    ) {
        /*
        |--------------------------------------------------------------------------
        | 現在の店舗
        |--------------------------------------------------------------------------
        */

        $shop = auth()->user()->currentShop();

        /*
        |--------------------------------------------------------------------------
        | 店舗チェック
        |--------------------------------------------------------------------------
        */

        abort_unless(
            $shopVideo->shop_id === $shop->id,
            403
        );

        /*
        |--------------------------------------------------------------------------
        | 出品者確認・承認
        |--------------------------------------------------------------------------
        */

        $workflow->approveSellerReview(
            $shopVideo
        );

        /*
        |--------------------------------------------------------------------------
        | 完了
        |--------------------------------------------------------------------------
        */

        return redirect()
            ->route(
                'seller.shop-videos.show',
                $shopVideo
            )
            ->with(
                'success',
                '加工後動画を承認しました。管理者審査へ進みます。'
            );
    }

    public function sellerReviewApprove(ShopVideoDraft $shopVideo)
    {
        $shopVideo->update([
            'seller_review_status' => ShopVideoDraft::SELLER_REVIEW_APPROVED,
            'seller_reviewed_at' => now(),
            'seller_reviewed_by' => auth()->id(),

            // 管理者審査へ
            'review_status' => ShopVideoDraft::REVIEW_PENDING,
            'workflow_stage' => ShopVideoDraft::STAGE_REVIEW,
        ]);

        return redirect()
            ->route(
                'seller.shop-videos.show',
                ['shopVideo' => $shopVideo->id]
            )
            ->with(
                'success',
                '動画を承認しました。管理者審査へ進みます。'
            );
    }




}
