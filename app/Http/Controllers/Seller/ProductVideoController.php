<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductVideoDraft;
use App\Models\ShopVideoBgm;
use App\Services\Video\Storage\ProductVideoStorageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Throwable;
use App\Jobs\AnalyzeProductVideoJob;
use App\Services\Video\Workflow\ProductVideoWorkflowService;

class ProductVideoController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Constructor
    |--------------------------------------------------------------------------
    */

    public function __construct(
        protected ProductVideoStorageService $storage
    ) {
    }


    /*
    |--------------------------------------------------------------------------
    | Create
    |--------------------------------------------------------------------------
    |
    | 商品YouTube動画登録画面
    |
    */

    public function create(Product $product)
    {
        $shop = auth()->user()->currentShop();

        if (
            !$shop ||
            $product->shop_id !== $shop->id
        ) {
            abort(403);
        }

        return view(
            'sellers.products.youtube_video.create',
            compact('product')
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Store
    |--------------------------------------------------------------------------
    |
    | 商品YouTube動画登録
    |
    */

    public function store(
        Request $request,
        Product $product
    ) {
        $shop = auth()->user()->currentShop();

        /*
        |--------------------------------------------------------------------------
        | 商品所有確認
        |--------------------------------------------------------------------------
        */

        if (
            !$shop ||
            $product->shop_id !== $shop->id
        ) {
            abort(403);
        }


        /*
        |--------------------------------------------------------------------------
        | Validation
        |--------------------------------------------------------------------------
        */

        $validated = $request->validate([

            'video' => [
                'required',
                'file',
                'mimes:mp4,mov,webm',
                'max:1048576',
            ],

            'title' => [
                'nullable',
                'string',
                'max:255',
            ],

            'description' => [
                'nullable',
                'string',
            ],

        ]);


        /*
        |--------------------------------------------------------------------------
        | 同一商品の動画登録ロック
        |--------------------------------------------------------------------------
        |
        | 連打・別タブ・同時POSTによる二重登録を防止
        |
        */

        $lock = Cache::lock(
            'product-video-store-shop-' . $shop->id,
            30
        );


        if (!$lock->get()) {

            return back()
                ->withInput()
                ->with(
                    'error',
                    '動画登録処理中です。しばらくお待ちください。'
                );
        }


        $originalPath = null;



        try {

            /*
            |--------------------------------------------------------------------------
            | 現在処理中の動画確認
            |--------------------------------------------------------------------------
            |
            | AI審査・動画加工・Previewのいずれかが
            | 未完了の場合、新しい動画登録を禁止
            |
            */

            $latestVideo =
                ProductVideoDraft::where(
                    'product_id',
                    $product->id
                )
                ->where('shop_id', $shop->id)
                ->latest('id')
                ->first();


            $processingVideo = null;


            if ($latestVideo) {

                if (
                    in_array(
                        $latestVideo->ai_status,
                        [
                            ProductVideoDraft::AI_PENDING,
                            ProductVideoDraft::AI_PROCESSING,
                        ],
                        true
                    )
                    ||
                    in_array(
                        $latestVideo->process_status,
                        [
                            ProductVideoDraft::PROCESS_WAITING,
                            ProductVideoDraft::PROCESS_RUNNING,
                        ],
                        true
                    )
                    ||
                    in_array(
                        $latestVideo->preview_status,
                        [
                            ProductVideoDraft::PREVIEW_WAITING,
                            ProductVideoDraft::PREVIEW_GENERATING,
                        ],
                        true
                    )
                ) {
                    $processingVideo = $latestVideo;
                }
            }


            if ($processingVideo) {

                return back()
                    ->withInput()
                    ->with(
                        'error',
                        '現在、別の動画を処理中です。処理が完了するまで新しい動画を登録できません。'
                    );
            }

            /*
            |--------------------------------------------------------------------------
            | 1日あたりの動画登録数制限
            |--------------------------------------------------------------------------
            |
            | 1ショップにつき1日3本まで
            |
            */

            $todayStart = now()->startOfDay();
            $todayEnd = now()->endOfDay();

            $todayVideoCount =
                ProductVideoDraft::where(
                    'shop_id',
                    $shop->id
                )
                ->whereBetween(
                    'created_at',
                    [
                        $todayStart,
                        $todayEnd,
                    ]
                )
                ->count();


            if ($todayVideoCount >= 3) {

                return back()
                    ->withInput()
                    ->with(
                        'error',
                        '本日の商品SNS動画登録上限（3本）に達しています。'
                    );
            }

            /*
            |--------------------------------------------------------------------------
            | S3
            |--------------------------------------------------------------------------
            |
            | オリジナル動画をS3へ保存
            |
            */

            $originalPath =
                $this->storage->storeOriginal(
                    $request->file('video')
                );


            /*
            |--------------------------------------------------------------------------
            | ProductVideoDraft
            |--------------------------------------------------------------------------
            */

            $video = DB::transaction(
                function () use (
                    $product,
                    $shop,
                    $validated,
                    $originalPath
                ) {

                    /*
                    |--------------------------------------------------------------------------
                    | Transaction内でも再確認
                    |--------------------------------------------------------------------------
                    |
                    | ロック取得後に状態が変化していないか確認
                    |
                    */

                    $latestVideo =
                        ProductVideoDraft::where(
                            'product_id',
                            $product->id
                        )
                        ->where('shop_id', $shop->id)
                        ->latest('id')
                        ->lockForUpdate()
                        ->first();


                    if (
                        $latestVideo
                        &&
                        (
                            in_array(
                                $latestVideo->ai_status,
                                [
                                    ProductVideoDraft::AI_PENDING,
                                    ProductVideoDraft::AI_PROCESSING,
                                ],
                                true
                            )
                            ||
                            in_array(
                                $latestVideo->process_status,
                                [
                                    ProductVideoDraft::PROCESS_WAITING,
                                    ProductVideoDraft::PROCESS_RUNNING,
                                ],
                                true
                            )
                            ||
                            in_array(
                                $latestVideo->preview_status,
                                [
                                    ProductVideoDraft::PREVIEW_WAITING,
                                    ProductVideoDraft::PREVIEW_GENERATING,
                                ],
                                true
                            )
                        )
                    ) {

                        throw new \RuntimeException(
                            '現在、別の動画を処理中です。処理が完了するまで新しい動画を登録できません。'
                        );
                    }


                    return ProductVideoDraft::create([

                        /*
                        |--------------------------------------------------------------------------
                        | Product
                        |--------------------------------------------------------------------------
                        */

                        'product_id' =>
                            $product->id,


                        /*
                        |--------------------------------------------------------------------------
                        | Shop
                        |--------------------------------------------------------------------------
                        */

                        'shop_id' =>
                            $shop->id,


                        /*
                        |--------------------------------------------------------------------------
                        | Created By
                        |--------------------------------------------------------------------------
                        */

                        'created_by' =>
                            auth()->id(),


                        /*
                        |--------------------------------------------------------------------------
                        | 基本
                        |--------------------------------------------------------------------------
                        */

                        'title' =>
                            $validated['title']
                            ?? $product->name,

                        'description' =>
                            $validated['description']
                            ?? $product->description,


                        /*
                        |--------------------------------------------------------------------------
                        | Original Movie
                        |--------------------------------------------------------------------------
                        */

                        'original_movie' =>
                            $originalPath,


                        /*
                        |--------------------------------------------------------------------------
                        | AI
                        |--------------------------------------------------------------------------
                        */

                        'ai_status' =>
                            ProductVideoDraft::AI_PENDING,


                        /*
                        |--------------------------------------------------------------------------
                        | Video Processing
                        |--------------------------------------------------------------------------
                        */

                        'process_status' =>
                            ProductVideoDraft::PROCESS_WAITING,


                        /*
                        |--------------------------------------------------------------------------
                        | Preview
                        |--------------------------------------------------------------------------
                        */

                        'preview_status' =>
                            ProductVideoDraft::PREVIEW_WAITING,


                        /*
                        |--------------------------------------------------------------------------
                        | Seller Review
                        |--------------------------------------------------------------------------
                        */

                        'seller_review_status' =>
                            ProductVideoDraft::SELLER_REVIEW_PENDING,


                        /*
                        |--------------------------------------------------------------------------
                        | Admin Review
                        |--------------------------------------------------------------------------
                        */

                        'review_status' =>
                            ProductVideoDraft::REVIEW_PENDING,


                        /*
                        |--------------------------------------------------------------------------
                        | YouTube
                        |--------------------------------------------------------------------------
                        */

                        'youtube_status' =>
                            ProductVideoDraft::YOUTUBE_NONE,


                        /*
                        |--------------------------------------------------------------------------
                        | Workflow
                        |--------------------------------------------------------------------------
                        */

                        'workflow_stage' =>
                            ProductVideoDraft::STAGE_AI,


                        /*
                        |--------------------------------------------------------------------------
                        | Active
                        |--------------------------------------------------------------------------
                        */

                        'is_active' => false,

                    ]);
                }
            );


            /*
            |--------------------------------------------------------------------------
            | AI Job
            |--------------------------------------------------------------------------
            */

            \Log::info(
                'ProductVideo AI job dispatch',
                [
                    'video_id' => $video->id,
                    'product_id' => $product->id,
                ]
            );


            AnalyzeProductVideoJob::dispatch(
                $video->id
            );


            /*
            |--------------------------------------------------------------------------
            | 完了
            |--------------------------------------------------------------------------
            */

            return redirect()
                ->route(
                    'seller.products.youtube-video.show',
                    [
                        'product' => $product->id,
                        'video' => $video->id,
                    ]
                )
                ->with(
                    'success',
                    '商品動画を登録しました。AI審査へ進みます。'
                );


        } catch (Throwable $e) {

            /*
            |--------------------------------------------------------------------------
            | S3 Cleanup
            |--------------------------------------------------------------------------
            */

            if ($originalPath) {

                try {

                    $this->storage->delete(
                        $originalPath
                    );

                } catch (Throwable $cleanupException) {

                    report(
                        $cleanupException
                    );
                }
            }


            report($e);


            return back()
                ->withInput()
                ->with(
                    'error',
                    $e->getMessage()
                );


        } finally {

            /*
            |--------------------------------------------------------------------------
            | Lock Release
            |--------------------------------------------------------------------------
            */

            optional($lock)->release();

        }
    }

    /*
    |--------------------------------------------------------------------------
    | Start Processing
    |--------------------------------------------------------------------------
    |
    | 商品SNS動画の加工開始
    |
    */

    public function startProcessing(
        Request $request,
        Product $product,
        ProductVideoDraft $video
    ) {
        $shop = auth()->user()->currentShop();

        /*
        |--------------------------------------------------------------------------
        | 商品所有確認
        |--------------------------------------------------------------------------
        */

        if (
            !$shop ||
            $product->shop_id !== $shop->id
        ) {
            abort(403);
        }


        /*
        |--------------------------------------------------------------------------
        | Video ownership
        |--------------------------------------------------------------------------
        */

        if (
            $video->product_id !== $product->id ||
            $video->shop_id !== $shop->id
        ) {
            abort(404);
        }


        /*
        |--------------------------------------------------------------------------
        | AI承認確認
        |--------------------------------------------------------------------------
        */

        if (
            $video->ai_status
                !== ProductVideoDraft::AI_APPROVED
        ) {
            return back()
                ->with(
                    'error',
                    'AI審査が承認された動画のみ加工を開始できます。'
                );
        }


        /*
        |--------------------------------------------------------------------------
        | 加工モードバリデーション
        |--------------------------------------------------------------------------
        |
        | 現在対応している加工モード
        |
        | V1 = ロゴ
        | V2 = ロゴ + BGM + エンディング
        |
        */

        $request->validate([
            'edit_mode' => [
                'required',
                'in:'
                    . ProductVideoDraft::EDIT_MODE_V1
                    . ','
                    . ProductVideoDraft::EDIT_MODE_V2,
            ],

            'bgm_id' => [
                'nullable',
                'integer',
                'exists:shop_video_bgms,id',
            ],
        ]);


        /*
        |--------------------------------------------------------------------------
        | 加工モード取得
        |--------------------------------------------------------------------------
        */

        $editMode = $request->input('edit_mode');

        $bgmId = $request->input('bgm_id');

        if (
            $editMode === ProductVideoDraft::EDIT_MODE_V2
            && empty($bgmId)
        ) {
            return back()
                ->withInput()
                ->with(
                    'error',
                    'V2ではBGMを選択してください。'
                );
        }

                /*
        |--------------------------------------------------------------------------
        | V2 BGM確認
        |--------------------------------------------------------------------------
        |
        | V2では有効なBGMのみ使用可能
        |
        */

        if (
            $editMode === ProductVideoDraft::EDIT_MODE_V2
            && !empty($bgmId)
        ) {

            $bgmExists = ShopVideoBgm::query()
                ->where('id', $bgmId)
                ->where('is_active', true)
                ->exists();

            if (!$bgmExists) {
                return back()
                    ->withInput()
                    ->with(
                        'error',
                        '選択したBGMは現在使用できません。'
                    );
            }
        }


        /*
        |--------------------------------------------------------------------------
        | 加工開始ロック
        |--------------------------------------------------------------------------
        |
        | 二重クリック・別タブ・同時POSTによる
        | 重複した動画加工開始を防止
        |
        */

        $started = DB::transaction(
            function () use (
                $video,
                $editMode,
                $bgmId
            ) {

                /*
                |--------------------------------------------------------------------------
                | DB Lock
                |--------------------------------------------------------------------------
                */

                $lockedVideo =
                    ProductVideoDraft::where(
                        'id',
                        $video->id
                    )
                    ->lockForUpdate()
                    ->first();


                /*
                |--------------------------------------------------------------------------
                | 念のため存在確認
                |--------------------------------------------------------------------------
                */

                if (!$lockedVideo) {

                    throw new \RuntimeException(
                        '動画が見つかりません。'
                    );
                }


                /*
                |--------------------------------------------------------------------------
                | AI承認確認
                |--------------------------------------------------------------------------
                */

                if (
                    $lockedVideo->ai_status
                        !== ProductVideoDraft::AI_APPROVED
                ) {

                    return false;
                }


                /*
                |--------------------------------------------------------------------------
                | 加工待ち確認
                |--------------------------------------------------------------------------
                */

                if (
                    $lockedVideo->process_status
                        !== ProductVideoDraft::PROCESS_WAITING
                ) {

                    return false;
                }


                /*
                |--------------------------------------------------------------------------
                | 加工開始済み確認
                |--------------------------------------------------------------------------
                |
                | 最初のPOSTだけが加工モードを設定できる
                |
                */

                if (
                    $lockedVideo->edit_mode
                        !== ProductVideoDraft::EDIT_MODE_PENDING
                ) {

                    return false;
                }


                /*
                |--------------------------------------------------------------------------
                | V1 / V2 加工開始
                |--------------------------------------------------------------------------
                */

                $lockedVideo->update([
                    'edit_mode' => $editMode,
                    'bgm_id' => $bgmId,
                ]);


                return true;
            }
        );


        /*
        |--------------------------------------------------------------------------
        | 加工開始できなかった場合
        |--------------------------------------------------------------------------
        */

        if (!$started) {

            return back()
                ->with(
                    'error',
                    'この動画はすでに加工開始済み、または現在加工を開始できる状態ではありません。'
                );
        }


        /*
        |--------------------------------------------------------------------------
        | ProductVideoProcessing Job
        |--------------------------------------------------------------------------
        */

        \Log::info(
            'ProductVideo processing job dispatch',
            [
                'video_id' => $video->id,
                'product_id' => $product->id,
                'edit_mode' => $editMode,
            ]
        );

        \App\Jobs\ProcessProductVideoJob::dispatch(
            $video->id
        );


        /*
        |--------------------------------------------------------------------------
        | 完了
        |--------------------------------------------------------------------------
        */

        return redirect()
            ->route(
                'seller.products.youtube-video.show',
                [
                    'product' => $product->id,
                    'video' => $video->id,
                ]
            )
            ->with(
                'success',
                '動画加工を開始しました。'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | Show
    |--------------------------------------------------------------------------
    |
    | 商品YouTube動画の状態表示
    |
    */

    
    public function show(
        Product $product,
        ProductVideoDraft $video
    ) {
        $shop = auth()->user()->currentShop();


        /*
        |--------------------------------------------------------------------------
        | 商品所有確認
        |--------------------------------------------------------------------------
        */

        if (
            !$shop ||
            $product->shop_id !== $shop->id
        ) {
            abort(403);
        }


        /*
        |--------------------------------------------------------------------------
        | Video ownership
        |--------------------------------------------------------------------------
        */

        if (
            $video->product_id !== $product->id ||
            $video->shop_id !== $shop->id
        ) {
            abort(404);
        }


        /*
        |--------------------------------------------------------------------------
        | Preview URL
        |--------------------------------------------------------------------------
        */

        $previewUrl = null;

        if (
            $video->process_status
                === ProductVideoDraft::PROCESS_COMPLETED &&
            !empty($video->processed_movie) &&
            $this->storage->exists(
                $video->processed_movie
            )
        ) {
            $previewUrl = $this->storage->temporaryUrl(
                $video->processed_movie,
                30
            );
        }

        /*
        |--------------------------------------------------------------------------
        | BGM一覧
        |--------------------------------------------------------------------------
        |
        | V2動画加工で使用するBGM
        |
        */

        $bgms = ShopVideoBgm::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        $bgmUrls = [];

        foreach ($bgms as $bgm) {

            if (
                !empty($bgm->file_path) &&
                $this->storage->exists($bgm->file_path)
            ) {
                $bgmUrls[$bgm->id] =
                    $this->storage->temporaryUrl(
                        $bgm->file_path,
                        30
                    );
            }
        }


        return view(
            'sellers.products.youtube_video.show',
            compact(
                'product',
                'video',
                'previewUrl',
                'bgms',
                'bgmUrls'
            )
        );
    }
    


    /*
    |--------------------------------------------------------------------------
    | Seller Review
    |--------------------------------------------------------------------------
    |
    | 商品SNS動画の出品者確認
    |
    */

    public function completeSellerReview(
        Product $product,
        ProductVideoDraft $video,
        ProductVideoWorkflowService $workflow
    ) {
        $shop = auth()->user()->currentShop();

        /*
        |--------------------------------------------------------------------------
        | 商品所有確認
        |--------------------------------------------------------------------------
        */

        if (
            !$shop ||
            $product->shop_id !== $shop->id
        ) {
            abort(403);
        }


        /*
        |--------------------------------------------------------------------------
        | Video ownership
        |--------------------------------------------------------------------------
        */

        if (
            $video->product_id !== $product->id ||
            $video->shop_id !== $shop->id
        ) {
            abort(404);
        }


        /*
        |--------------------------------------------------------------------------
        | Preview完了確認
        |--------------------------------------------------------------------------
        */

        if (
            $video->preview_status
                !== ProductVideoDraft::PREVIEW_COMPLETED
        ) {
            return back()
                ->with(
                    'error',
                    'Previewが完了した動画のみ出品者確認を完了できます。'
                );
        }


        /*
        |--------------------------------------------------------------------------
        | 出品者確認状態確認
        |--------------------------------------------------------------------------
        */

        if (
            $video->seller_review_status
                !== ProductVideoDraft::SELLER_REVIEW_PENDING
        ) {
            return back()
                ->with(
                    'error',
                    'この動画は出品者確認を完了できる状態ではありません。'
                );
        }


        /*
        |--------------------------------------------------------------------------
        | 出品者確認完了
        |--------------------------------------------------------------------------
        */

        $workflow->completeSellerReview(
            $video,
            auth()->id()
        );


        /*
        |--------------------------------------------------------------------------
        | 完了
        |--------------------------------------------------------------------------
        */

        return redirect()
            ->route(
                'seller.products.youtube-video.show',
                [
                    'product' => $product->id,
                    'video' => $video->id,
                ]
            )
            ->with(
                'success',
                '動画の出品者確認が完了しました。管理者審査へ進みます。'
            );
    }
}