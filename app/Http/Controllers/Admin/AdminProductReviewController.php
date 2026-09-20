<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Product;
use App\Models\ProductDraft;
use App\Models\ProductImageReview;
use App\Models\ActivityLog;
use App\Helpers\Audit;

use Illuminate\Support\Facades\Log;

class AdminProductReviewController extends Controller
{
    public function index(Request $request)
    {
        $query = ProductDraft::with([

            'shop',

            'originalCreator',

            'lastSubmitter',

            'imageReviews',

        ])->where(
            'status',
            'admin_pending'
        );

        /*
        |--------------------------------------------------------------------------
        | AI画像状態
        |--------------------------------------------------------------------------
        */
        if ($request->filled('image_ai_status')) {

            $query->where(

                'image_ai_status',

                $request->image_ai_status

            );

        }

        /*
        |--------------------------------------------------------------------------
        | AI総合状態
        |--------------------------------------------------------------------------
        */
        if ($request->filled('overall_ai_status')) {

            $query->where(

                'overall_ai_status',

                $request->overall_ai_status

            );

        }

        /*
        |--------------------------------------------------------------------------
        | NG画像あり
        |--------------------------------------------------------------------------
        */
        if ($request->boolean('has_ai_ng')) {

            $query->where(

                'image_ai_ng_count',

                '>',

                0

            );

        }

        /*
        |--------------------------------------------------------------------------
        | AI審査完了
        |--------------------------------------------------------------------------
        */
        if ($request->boolean('ai_completed')) {

            $query->whereIn(

                'image_ai_status',

                [

                    'approved',

                    'warning',

                ]

            );

        }

        $summary = [

            'total' => ProductDraft::where(
                'status',
                'admin_pending'
            )->count(),

            'new' => ProductDraft::where(
                'status',
                'admin_pending'
            )
            ->whereNull('product_id')
            ->count(),

            'update' => ProductDraft::where(
                'status',
                'admin_pending'
            )
            ->whereNotNull('product_id')
            ->count(),

            'movie' => ProductDraft::where(
                'status',
                'admin_pending'
            )
            ->whereNotNull('movie_file')
            ->count(),

            'today' => ProductDraft::where(
                'status',
                'admin_pending'
            )
            ->whereDate(
                'created_at',
                today()
            )
            ->count(),

            'week' => ProductDraft::where(
                'status',
                'admin_pending'
            )
            ->where(
                'created_at',
                '>=',
                now()->subDays(7)
            )
            ->count(),
        ];

        $summary['ai_ng_images'] =
            ProductImageReview::where(
                'status',
                'rejected'
            )
            ->whereHas('draft', function ($q) {

                $q->where(
                    'status',
                    'admin_pending'
                );

            })
            ->count(); 

         $summary['ai_ng_products'] =
            ProductImageReview::where(
                'status',
                'rejected'
            )
            ->whereHas('draft', function ($q) {

                $q->where(
                    'status',
                    'admin_pending'
                );

            })
            ->distinct('draft_id')
            ->count('draft_id'); 

        $drafts = $query
        ->latest()
        ->paginate(20)
        ->withQueryString();         

        return view(
            'admin.admin_product_review.index',
            compact(
                'drafts',
                'summary',
                'query'
            )
        );
    }

    public function show($id)
    {
        // $draft = ProductDraft::findOrFail($id);

        $draft = ProductDraft::with([
            'originalCreator',
            'lastSubmitter',
            'ownerReviewer',
        ])->findOrFail($id);

        $product = null;

        $beforeDraft = $draft->toArray();

        if ($draft->product_id) {

            $product = Product::find(
                $draft->product_id
            );

        }

        $reviews = ProductImageReview::where(
            'draft_id',
            $draft->id
        )->get();

        $aiSummary = [

            'total' => $reviews->count(),

            'ok' => $reviews
                ->where('status', 'approved')
                ->count(),

            'ng' => $reviews
                ->where('status', 'rejected')
                ->count(),

            'pending' => $reviews
                ->where('status', 'pending')
                ->count(),

            'maxRisk' => $reviews->max('risk_score'),

            'status' => $draft->overall_ai_status,

        ];

        return view(
            'admin.admin_product_review.show',
            compact(
                'draft',
                'product',
                'reviews',
                'aiSummary'
            )
        );
    }

    public function approve(ProductDraft $draft)
    {

        /*
        |--------------------------------------------------------------------------
        | 承認前のDraft状態を保存
        |--------------------------------------------------------------------------
        */
        $beforeDraft = $draft->toArray();
        
        /*
        |--------------------------------------------------------------------------
        | 更新商品
        |--------------------------------------------------------------------------
        */
        if ($draft->product_id) {

            $product = Product::findOrFail(
                $draft->product_id
            );

            $beforeProduct = $product->toArray();

            $product->update([

                'name' => $draft->name,

                'description' => $draft->description,

                'price' => $draft->price,

                'shipping_fee' => $draft->shipping_fee,

                'stock' => $draft->stock,

                'cover_img' => $draft->cover_img,

                'cover_img2' => $draft->cover_img2,

                'cover_img3' => $draft->cover_img3,

                'movie' => $draft->movie,

                'movie_file' => $draft->movie_file,

                /*
                |--------------------------------------------------------------------------
                | 申請者情報
                |--------------------------------------------------------------------------
                */
                'last_submitted_by' => $draft->last_submitted_by,



                'status' => 1,

                'review_status' => 'approved',

                'approved_at' => now(),
            ]);

            $afterProduct = $product->fresh()->toArray();

        } else {

            /*
            |--------------------------------------------------------------------------
            | 新規商品
            |--------------------------------------------------------------------------
            */

            $beforeProduct = null;

            $product = Product::create([

                'shop_id' => $draft->shop_id,

                'draft_id' => $draft->id,

                'name' => $draft->name,

                'description' => $draft->description,

                'price' => $draft->price,

                'shipping_fee' => $draft->shipping_fee,

                'stock' => $draft->stock,

                'cover_img' => $draft->cover_img,

                'cover_img2' => $draft->cover_img2,

                'cover_img3' => $draft->cover_img3,

                'movie' => $draft->movie,

                'movie_file' => $draft->movie_file,

                /*
                |--------------------------------------------------------------------------
                | 作成者・申請者
                |--------------------------------------------------------------------------
                */
                'original_created_by'
                    => $draft->original_created_by,

                'last_submitted_by'
                    => $draft->last_submitted_by,

                'status' => 1,

                'review_status' => 'approved',

                'approved_at' => now(),
            ]);

            $afterProduct = $product->fresh()->toArray();

            $draft->update([
                'product_id' => $product->id,
            ]);

            $afterProduct = $product->fresh()->toArray();
        }

        $draft->update([

            'status' => 'approved',

            'admin_comment' => null,
        ]);

        $afterDraft = $draft->fresh()->toArray();

        Audit::log(

            action: 'admin_approved',

            target: $draft,

            before: $beforeDraft,

            after: $afterDraft,

            description: '管理者が商品申請を承認'

        );

        Audit::log(

            action: 'product_published',

            target: $product,

            before: $beforeProduct,

            after: $afterProduct,

            description: $draft->product_id
                ? '公開中商品を更新'
                : '新規商品を公開'

        );

        return redirect()
            ->route('admin.review.index')
            ->with(
                'success',
                '商品を承認しました'
            );
           
    }

    public function revision(
        Request $request,
        ProductDraft $draft
    )
    {
        $request->validate([

            'admin_comment' =>
                'required|string|max:1000',
        ]);

        $draft->update([

            'status' => 'admin_revision',

            'admin_comment' =>
                $request->admin_comment,
        ]);

        return back()->with(
            'success',
            '差戻しました'
        );
    }

    public function reject(
        Request $request,
        ProductDraft $draft
    )
    {

        $beforeDraft = $draft->toArray();

        $request->validate([

            'admin_comment' =>
                'required|string|max:1000',
        ]);

        $draft->update([

            'status' => 'rejected',

            'admin_comment' =>
                $request->admin_comment,
        ]);

        $afterDraft = $draft->fresh()->toArray();

        $product = Product::find(
            $draft->product_id
        );

        if ($product) {

            $product->update([

                'status' => 0,

                'review_status' => 'rejected',
            ]);
        }

        Audit::log(

            action: 'admin_rejected',

            target: $draft,

            before: $beforeDraft,

            after: $afterDraft,

            description: '管理者が商品申請を却下',

            extra: [

                'comment' => $request->admin_comment,

            ]

        );

        return back()->with(
            'success',
            '却下しました'
        );
    }

}
