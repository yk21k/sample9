<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use App\Models\ProductImageReview;
use App\Models\Product;
use App\Models\ProductDraft;
use App\Models\ProductReviewQueue;
use App\Models\ActivityLog;
use App\Models\ProductEditDraft;
use App\Models\TaxRate;
use Illuminate\Support\Facades\Log;
use App\Jobs\AnalyzeProductImageJob;


class ProductDraftController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | draft 一覧
    |--------------------------------------------------------------------------
    */
    public function index()
    {
        $shop = auth()->user()->currentShop();

        if (!$shop) {
            abort(403);
        }

        $drafts = ProductDraft::with('product')
            ->where('shop_id', $shop->id)
            ->whereNotIn('status', [
                'approved'
            ])
            ->latest()
            ->paginate(20);

        return view(
            'sellers.product_drafts.index',
            compact('drafts')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | 編集画面
    |--------------------------------------------------------------------------
    */
    public function edit(ProductDraft $draft)
    {
        // dd($draft);
        $shop = auth()->user()->currentShop();

        if (!$shop) {
            abort(403);
        }

        if ($draft->shop_id !== $shop->id) {
            abort(403);
        }

        return view(
            'sellers.product_drafts.edit',
            compact('draft')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | 更新
    |--------------------------------------------------------------------------
    */
    public function update(
        Request $request,
        ProductDraft $draft
    ) {

        $shop = auth()->user()->currentShop();

        if (!$shop) {
            abort(403);
        }

        if ($draft->shop_id !== $shop->id) {
            abort(403);
        }

        $validated = $request->validate([

            'name' => 'required|string|max:255',

            'description' => 'nullable|string',

            'price' => 'required|integer|min:0',

            'stock' => 'required|integer|min:0',

            'owner_comment' => 'nullable|string',

            'shipping_fee' => 'nullable|integer|min:0',

            'movie' => 'nullable|string',

            'product_attributes' => 'nullable|json',

            'movie_file' => 'nullable|file|mimes:mp4,mov,webm|max:51200',
        ]);

        /*
        |--------------------------------------------------------------------------
        | 画像
        |--------------------------------------------------------------------------
        */
        if ($request->hasFile('cover_img')) {

            $validated['cover_img'] = $request
                ->file('cover_img')
                ->store(
                    'product-drafts',
                    's3'
                );
        }

        if ($request->hasFile('cover_img2')) {

            $validated['cover_img2'] = $request
                ->file('cover_img2')
                ->store(
                    'product-drafts',
                    's3'
                );
        }

        if ($request->hasFile('cover_img3')) {

            $validated['cover_img3'] = $request
                ->file('cover_img3')
                ->store(
                    'product-drafts',
                    's3'
                );
        }

        if ($request->hasFile('movie_file')) {

            $validated['movie_file'] = $request
                ->file('movie_file')
                ->store(
                    'product-drafts/movies',
                    'public'
                );
        }

        if ($request->filled('product_attributes')) {

            $validated['product_attributes']
                = json_decode(
                    $request->product_attributes,
                    true
                );
        }

        $taxRate = TaxRate::current()?->rate ?? 0;

        $isTaxable = !empty(
            auth()->user()->currentShop()?->invoice_number
        );

        $price = (int) $validated['price'];

        $shipping = (int) ($validated['shipping_fee'] ?? 0);

        if ($isTaxable) {

            $displayPrice =
                floor($price + ($price * $taxRate));

            $displayShipping =
                floor($shipping + ($shipping * $taxRate));

        } else {

            $displayPrice = $price;

            $displayShipping = $shipping;
        }
        
        $validated['tax_rate'] = $taxRate;

        $validated['is_taxable'] = $isTaxable;

        $validated['display_price'] = $displayPrice;

        $validated['display_shipping_fee'] = $displayShipping;

        $validated['display_total_price'] =
            $displayPrice + $displayShipping;


        $draft->update($validated);

        // dd([
        //     'validated_cover_img' => $validated['cover_img'] ?? null,
        //     'draft_cover_img' => $draft->fresh()->cover_img,
        // ]);

        // dd($draft->wasChanged());

        /*
        |--------------------------------------------------------------------------
        | staff / manager が既存商品を編集したら即非公開
        |--------------------------------------------------------------------------
        */
        $member = auth()->user()->shopMember;

        if (
            $draft->product_id &&
            $member &&
            in_array($member->role, ['staff', 'manager'])
        ) {

            Product::where(
                'id',
                $draft->product_id
            )->update([

                'status' => 0,

                'review_status' => 'owner_pending',
            ]);
        }

        ActivityLog::create([

            'user_id' => auth()->id(),

            'shop_id' => $shop->id,

            'product_id' => $draft->product_id,

            'draft_id' => $draft->id,

            'action' => 'draft_updated',

            'target_type' => 'ProductDraft',

            'target_id' => $draft->id,

            'changes' => [

                'status' => $draft->status,

            ],

            'role' => optional(
                auth()->user()->shopMember
            )->role,

            'ip' => request()->ip(),

            'user_agent' => request()->userAgent(),

        ]);

        return back()->with(
            'success',
            '下書きを更新しました'
        );

    }

    /*
    |--------------------------------------------------------------------------
    | owner 承認
    |--------------------------------------------------------------------------
    */
    public function approve(ProductDraft $draft)
    {

        // dd([
        //     'draft_id' => $draft->id,
        //     'product_id' => $draft->product_id,
        // ]);

        // dd($draft->toArray());

        // dd($draft->toArray());

        $before = $draft->toArray();

        Log::info('APPROVE START',[
            'draft_id' => $draft->id,
            'time' => now(),
        ]);

        if ($draft->status === 'approved') {
            return back()->with(
                'error',
                '既に承認済みです'
            );
        }

        $shop = auth()->user()->currentShop();

        if (!$shop) {
            abort(403);
        }

        if ($draft->shop_id !== $shop->id) {
            abort(403);
        }

        $member = auth()->user()->shopMember;

        if (
            !$member ||
            $member->role !== 'owner'
        ) {
            abort(403);
        }

        // dd($draft->id);

        // dd([
        //     'draft_id' => $draft->id,
        //     'product_id' => $draft->product_id,
        // ]);



        $product = Product::find(
            $draft->product_id
        );

        // dd([
        //     'product_id' => $product->id,
        //     'cover_img' => $product->cover_img,
        //     'cover_img2' => $product->cover_img2,
        //     'cover_img3' => $product->cover_img3,
        // ]);

        /*
        |--------------------------------------------------------------------------
        | ProductImageReview生成
        |--------------------------------------------------------------------------
        */

        // $created = [];

        $reviewsToAnalyze = [];

        foreach ([
            'cover_img',
            'cover_img2',
            'cover_img3',
        ] as $field) {

            $imagePath = $draft->$field;

            if (!$imagePath) {
                continue;
            }

            $newHash = md5($imagePath);

            $review = ProductImageReview::firstOrNew([
                'draft_id' => $draft->id,
                'image_type' => $field,
            ]);

            // 既存で変更なしならスキップ
            if ($review->exists && $review->last_ai_hash === $newHash) {
                continue;
            }

            $review->image_path = $imagePath;
            $review->last_ai_hash = $newHash;
            $review->status = 'pending';
            $review->risk_score = 0;
            $review->moderation_labels = null;
            $review->reviewed_at = null;

            $review->save();

            // ★変更されたものだけ追加
            $reviewsToAnalyze[] = $review;
        } 

        foreach ($reviewsToAnalyze as $review) {
            AnalyzeProductImageJob::dispatch($review->id);
        }

        /*
        |--------------------------------------------------------------------------
        | draft 更新
        |--------------------------------------------------------------------------
        */
        $draft->update([
            'status' => 'admin_pending',
            'approved_at' => now(),
        ]);

        $after = $draft->fresh()->toArray();

        /*
        |--------------------------------------------------------------------------
        | log
        |--------------------------------------------------------------------------
        */
        Audit::log(

            action: 'owner_approved',

            target: $draft,

            before: $before,

            after: $after,

            description: 'オーナーが商品申請を承認'

        );

        return redirect()
            ->route('seller.product_drafts.index')
            ->with(
                'success',
                '商品を運営審査へ送信しました'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | 差し戻し
    |--------------------------------------------------------------------------
    */
    public function reject(
        Request $request,
        ProductDraft $draft
    ) {

        $shop = auth()->user()->currentShop();

        if (!$shop) {
            abort(403);
        }

        if ($draft->shop_id !== $shop->id) {
            abort(403);
        }

        $member = auth()->user()->shopMember;

        if (
            !$member ||
            $member->role !== 'owner'
        ) {
            abort(403);
        }

        $draft->update([

            'status' => 'rejected',

            'owner_comment' => $request->owner_comment,
        ]);

        ActivityLog::create([

            'user_id' => auth()->id(),

            'action' => 'draft_rejected',

            'target_type' => 'ProductDraft',

            'target_id' => $draft->id,

            'changes' => [

                'owner_comment' =>
                    $request->owner_comment,
            ],

            'role' => optional(
                auth()->user()->shopMember
            )->role,
        ]);

        return back()->with(
            'success',
            '商品を差し戻しました'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Draft 削除
    |--------------------------------------------------------------------------
    */
    public function destroy(ProductDraft $draft)
    {
        $shop = auth()->user()->currentShop();

        if (!$shop) {
            abort(403);
        }

        if ($draft->shop_id !== $shop->id) {
            abort(403);
        }

        $member = auth()->user()->shopMember;

        if (
            !$member ||
            !in_array(
                $member->role,
                ['owner', 'manager', 'staff']
            )
        ) {
            abort(403);
        }

        ActivityLog::create([

            'user_id' => auth()->id(),

            'action' => 'draft_deleted',

            'target_type' => 'ProductDraft',

            'target_id' => $draft->id,

            'changes' => $draft->toArray(),

            'role' => $member->role,
        ]);

        $draft->delete();

        return redirect()
            ->route('seller.product_drafts.index')
            ->with(
                'success',
                'Draftを削除しました'
        );
    }
}