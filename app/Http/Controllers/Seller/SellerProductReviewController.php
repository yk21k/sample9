<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class SellerProductReviewController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | ownerレビュー一覧
    |--------------------------------------------------------------------------
    */
    public function index()
    {
        // dd('Hello');
        $shop = auth()->user()->currentShop();

        $drafts = ProductEditDraft::with('product')
            ->where('shop_id', $shop->id)
            ->where('status', 'owner_pending')
            ->latest()
            ->paginate(20);

        return view(
            'sellers.product_reviews.index',
            compact('drafts')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | owner承認
    |--------------------------------------------------------------------------
    */
    public function approve(Product $product)
    {
        $shop = auth()->user()->currentShop();

        if (!$shop) {
            abort(403);
        }

        if ($product->shop_id !== $shop->id) {
            abort(403);
        }

        /*
        |--------------------------------------------------------------------------
        | queue 作成
        |--------------------------------------------------------------------------
        */

        ProductReviewQueue::create([

            'product_id' => $product->id,

            'user_id' => auth()->id(),

            'status' => 'pending',
        ]);

        $product->update([

            /*
            |--------------------------------------------------------------------------
            | admin審査待ちへ
            |--------------------------------------------------------------------------
            */
            'review_status' => 'admin_pending',

            'owner_reviewed_by' => auth()->id(),

            'owner_reviewed_at' => now(),
        ]);

        /*
        |--------------------------------------------------------------------------
        | log
        |--------------------------------------------------------------------------
        */
        ActivityLog::create([

            'user_id' => auth()->id(),

            'action' => 'owner_approved',

            'target_type' => 'Product',

            'target_id' => $product->id,

            'changes' => [
                'review_status' => 'admin_pending'
            ],

            'role' => 'owner',
        ]);

        return back()->with(
            'success',
            '運営審査へ送信しました'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | owner差し戻し
    |--------------------------------------------------------------------------
    */
    public function reject(
        Request $request,
        Product $product
    ) {

        $shop = auth()->user()->currentShop();

        if (!$shop) {
            abort(403);
        }

        if ($product->shop_id !== $shop->id) {
            abort(403);
        }

        $request->validate([
            'owner_note' => 'required|string|max:1000',
        ]);

        $product->update([

            /*
            |--------------------------------------------------------------------------
            | managerへ差し戻し
            |--------------------------------------------------------------------------
            */
            'review_status' => 'owner_rejected',

            'owner_note' => $request->owner_note,

            'owner_reviewed_by' => auth()->id(),

            'owner_reviewed_at' => now(),
        ]);

        ActivityLog::create([
            'user_id' => auth()->id(),

            'action' => 'owner_rejected',

            'target_type' => 'Product',

            'target_id' => $product->id,

            'changes' => [
                'owner_note' => $request->owner_note,
            ],

            'role' => 'owner',
        ]);

        return back()->with(
            'success',
            '差し戻しました'
        );
    }
}