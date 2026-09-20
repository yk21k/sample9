<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use App\Services\ProductService;
use App\Models\ProductDraft;
use App\Helpers\Audit;

// staff manager用
class ProductsController extends Controller
{
    protected $service;

    public function __construct(ProductService $service)
    {
        $this->service = $service;
    }

    /*
    |--------------------------------------------------------------------------
    | 商品一覧
    |--------------------------------------------------------------------------
    */
    public function index()
    {
        $shop = auth()->user()->currentShop();

        if (!$shop) {
            abort(403);
        }

        $products = Product::with('draft')
            ->where('shop_id', $shop->id)
            // ->where('status', 1)
            // ->where('review_status', 'approved')
            ->latest()
            ->paginate(20);

   

        return view(
            'sellers.products.index',
            compact('products')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | 新規作成画面
    |--------------------------------------------------------------------------
    */
    public function create()
    {
        $shop = auth()->user()->currentShop();

        if (!$shop) {
            abort(403);
        }

        return view('sellers.products.create');
    }

    /*
    |--------------------------------------------------------------------------
    | 保存
    |--------------------------------------------------------------------------
    */
    public function store(Request $request)
    {
        $shop = auth()->user()->currentShop();

        if (!$shop) {
            abort(403);
        }

        /*
        |--------------------------------------------------------------------------
        | バリデーション
        |--------------------------------------------------------------------------
        */
        $validated = $request->validate([

            'name' => 'required|string|max:255',

            'description' => 'nullable|string',

            'price' => 'required|integer|min:0',

            'shipping_fee' => 'nullable|integer|min:0',

            'stock' => 'required|integer|min:1',

            /*
            |--------------------------------------------------------------------------
            | 画像
            |--------------------------------------------------------------------------
            */
            'cover_img' => 'nullable|image|max:5120',

            'cover_img2' => 'nullable|image|max:5120',

            'cover_img3' => 'nullable|image|max:5120',

            /*
            |--------------------------------------------------------------------------
            | 動画
            |--------------------------------------------------------------------------
            */
            'movie' => 'nullable|string|max:1000',

            'movie_upload' => 'nullable|file|mimes:mp4,mov,webm|max:51200',

            /*
            |--------------------------------------------------------------------------
            | 属性
            |--------------------------------------------------------------------------
            */
            'product_attributes' => 'nullable|array',
        ]);

        /*
        |--------------------------------------------------------------------------
        | 配送料25%制限
        |--------------------------------------------------------------------------
        */
        $maxShipping = floor($validated['price'] * 0.25);

        if (
            !empty($validated['shipping_fee']) &&
            $validated['shipping_fee'] > $maxShipping
        ) {
            return back()
                ->withErrors([
                    'shipping_fee' =>
                        "配送料は価格の25%（最大 {$maxShipping} 円）までです"
                ])
                ->withInput();
        }

        /*
        |--------------------------------------------------------------------------
        | 画像保存
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

        /*
        |--------------------------------------------------------------------------
        | 動画アップロード
        |--------------------------------------------------------------------------
        */
        if ($request->hasFile('movie_file')) {

            $validated['movie_file'] = $request
                ->file('movie_file')
                ->store(
                    'product-drafts/movies',
                    'public'
                );
        }

        /*
        |--------------------------------------------------------------------------
        | 消費税率固定保存
        |--------------------------------------------------------------------------
        */
        $taxRate = \App\Models\TaxRate::current()?->rate ?? 0;

        /*
        |--------------------------------------------------------------------------
        | 課税判定
        |--------------------------------------------------------------------------
        */
        $isTaxable = !empty($shop->invoice_number);

        // dd($validated);

        /*
        |--------------------------------------------------------------------------
        | draft 作成
        |--------------------------------------------------------------------------
        */
        $draft = ProductDraft::create([

            'shop_id' => $shop->id,

            'product_id' => null,

            'created_by' => auth()->id(),

            'original_created_by' => auth()->id(),

            'last_submitted_by' => auth()->id(),

            'name' => $validated['name'],

            'description' =>
                $validated['description'] ?? null,

            'price' =>
                $validated['price'],

            'shipping_fee' =>
                $validated['shipping_fee'] ?? 0,

            'stock' =>
                $validated['stock'],

            'cover_img' =>
                $validated['cover_img'] ?? null,

            'cover_img2' =>
                $validated['cover_img2'] ?? null,

            'cover_img3' =>
                $validated['cover_img3'] ?? null,

            'movie' =>
                $validated['movie'] ?? null,


                
            'movie_file' =>
                 $validated['movie_file']?? null,

            'product_attributes' =>
                $validated['product_attributes'] ?? null,

            /*
            |--------------------------------------------------------------------------
            | 税情報固定
            |--------------------------------------------------------------------------
            */
            'tax_rate' => $taxRate,

            'is_taxable' => $isTaxable,

            /*
            |--------------------------------------------------------------------------
            | owner 承認待ち
            |--------------------------------------------------------------------------
            */
            'status' => 'owner_pending',
        ]);



        /*
        |--------------------------------------------------------------------------
        | 操作ログ
        |--------------------------------------------------------------------------
        */
        Audit::log(

            action: 'draft_created',

            target: $draft,

            before: null,

            after: [

                'name' => $draft->name,

                'status' => $draft->status,

            ],

            description: '商品を新規申請'

        );

        return redirect()
            ->route('seller.products.index')
            ->with(
                'success',
                '商品申請を送信しました（オーナー承認待ち）'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | 編集画面
    |--------------------------------------------------------------------------
    */
    public function edit(Product $product)
    {
        $shop = auth()->user()->currentShop();

        if (
            !$shop ||
            $product->shop_id !== $shop->id
        ) {
            abort(403);
        }

        return view(
            'sellers.products.edit',
            compact('product')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | 更新
    |--------------------------------------------------------------------------
    */
    public function update(
        Request $request,
        Product $product
    ) {

        $shop = auth()->user()->currentShop();

        if (
            !$shop ||
            $product->shop_id !== $shop->id
        ) {
            abort(403);
        }

        $validated = $request->validate([

            'name' => 'required|string|max:255',

            'description' => 'nullable|string',

            'price' => 'required|integer|min:0',

            'shipping_fee' => 'nullable|integer|min:0',

            'stock' => 'required|integer|min:1',

            /*
            |--------------------------------------------------------------------------
            | 画像
            |--------------------------------------------------------------------------
            */
            'cover_img' => 'nullable|image|max:5120',

            'cover_img2' => 'nullable|image|max:5120',

            'cover_img3' => 'nullable|image|max:5120',

            /*
            |--------------------------------------------------------------------------
            | 動画
            |--------------------------------------------------------------------------
            */
            'movie' => 'nullable|string|max:1000',

            'movie_upload' => 'nullable|file|mimes:mp4,mov,webm|max:51200',

            /*
            |--------------------------------------------------------------------------
            | 属性
            |--------------------------------------------------------------------------
            */
            'product_attributes' => 'nullable|array',
        ]);

        /*
        |--------------------------------------------------------------------------
        | 配送料25%制限
        |--------------------------------------------------------------------------
        */
        $maxShipping = floor($validated['price'] * 0.25);

        if (
            !empty($validated['shipping_fee']) &&
            $validated['shipping_fee'] > $maxShipping
        ) {
            return back()
                ->withErrors([
                    'shipping_fee' =>
                        "配送料は価格の25%（最大 {$maxShipping} 円）までです"
                ])
                ->withInput();
        }

        $before = $product->toArray();

        /*
        |--------------------------------------------------------------------------
        | 画像保存
        |--------------------------------------------------------------------------
        */
        if ($request->hasFile('cover_img')) {

            $validated['cover_img'] = $request
                ->file('cover_img')
                ->store(
                    'products',
                    'public'
                );
        }

        if ($request->hasFile('cover_img2')) {

            $validated['cover_img2'] = $request
                ->file('cover_img2')
                ->store(
                    'products',
                    'public'
                );
        }

        if ($request->hasFile('cover_img3')) {

            $validated['cover_img3'] = $request
                ->file('cover_img3')
                ->store(
                    'products',
                    'public'
                );
        }

        /*
        |--------------------------------------------------------------------------
        | 動画アップロード
        |--------------------------------------------------------------------------
        */
        if ($request->hasFile('movie_file')) {

            $validated['movie_file'] = $request
                ->file('movie_file')
                ->store(
                    'product-drafts/movies',
                    'public'
                );
        }

        /*
        |--------------------------------------------------------------------------
        | role
        |--------------------------------------------------------------------------
        */
        $member = auth()->user()->shopMember;

        /*
        |--------------------------------------------------------------------------
        | manager/staff
        |--------------------------------------------------------------------------
        */
        if (
            $member &&
            in_array(
                $member->role,
                ['manager', 'staff']
            )
        ) {

             // dd('staff branch');
            /*
            |--------------------------------------------------------------------------
            | owner承認待ち
            |--------------------------------------------------------------------------
            */
            $validated['review_status']
                = 'owner_pending';

            /*
            |--------------------------------------------------------------------------
            | 非公開
            |--------------------------------------------------------------------------
            */
            $validated['status'] = 0;

            /*
            |--------------------------------------------------------------------------
            | owner承認解除
            |--------------------------------------------------------------------------
            */
            $validated['approved_by'] = null;

            $validated['approved_at'] = null;

            /*
            |--------------------------------------------------------------------------
            | 管理者審査も解除
            |--------------------------------------------------------------------------
            */
            $validated['reviewed_by'] = null;
            $validated['reviewed_at'] = null;

            /*
            |--------------------------------------------------------------------------
            | Product 更新
            |--------------------------------------------------------------------------
            */
            $this->service->updateWithLog(
                $product,
                $validated,
                auth()->user(),
                'seller_updated'
            );

            // dd($validated);

            // dd($request->all());


            $product->refresh();

            $after = $product->toArray();

            Audit::log(

                action: 'product_updated',

                target: $product,

                before: $before,

                after: $after,

                description: '担当者が商品を更新'

            );

            return back()->with(
                'success',
                '編集申請を送信しました'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | owner
        |--------------------------------------------------------------------------
        */
        $this->service->updateWithLog(
            $product,
            $validated,
            auth()->user(),
            'seller_updated'
        );

        /*
        |--------------------------------------------------------------------------
        | 更新後
        |--------------------------------------------------------------------------
        */
        $after = $product
            ->fresh()
            ->toArray();

        /*
        |--------------------------------------------------------------------------
        | Audit Log
        |--------------------------------------------------------------------------
        */
        Audit::log(

            action: 'product_updated',

            target: $product,

            before: $before,

            after: $after,

            description: 'オーナーが商品を更新'

        );

        return back()->with(
            'success',
            '更新しました'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | 削除
    |--------------------------------------------------------------------------
    */
    public function destroy(Product $product)
    {
        $shop = auth()->user()->currentShop();

        if (!$shop || $product->shop_id !== $shop->id) {
            abort(403);
        }

        $product->delete();

        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'deleted',
            'target_type' => 'Product',
            'target_id' => $product->id,
            'changes' => [],
            'role' => optional(auth()->user()->shopMember)->role,
        ]);

        return back()->with('success', '削除しました');
    }

    public function editDraft(Product $product)
    {
        $shop = auth()->user()->currentShop();

        if (
            !$shop ||
            $product->shop_id !== $shop->id
        ) {
            abort(403);
        }

        /*
        |--------------------------------------------------------------------------
        | 既存Draft取得
        |--------------------------------------------------------------------------
        */
        $draft = $product->drafts()
            ->whereIn('status', [
                'owner_pending',
                'rejected',
            ])
            ->latest()
            ->first();

        /*
        |--------------------------------------------------------------------------
        | 無ければ作成
        |--------------------------------------------------------------------------
        */
        if (!$draft) {

            $draft = ProductDraft::create([

                'shop_id' => $product->shop_id,

                'product_id' => $product->id,

                'created_by' => auth()->id(),

                /*
                |--------------------------------------------------------------------------
                | 作成者情報
                |--------------------------------------------------------------------------
                */
                'original_created_by'
                    => $product->original_created_by,

                'last_submitted_by'
                    => auth()->id(),

                'name' => $product->name,

                'description' => $product->description,

                'price' => $product->price,

                'shipping_fee' => $product->shipping_fee,

                'stock' => $product->stock,

                'cover_img' => $product->cover_img,

                'cover_img2' => $product->cover_img2,

                'cover_img3' => $product->cover_img3,

                'movie' => $product->movie,

                'movie_file' => $product->movie_file,

                'status' => 'owner_pending_public',
            ]);
        }



        return redirect()->route(
            'seller.product_drafts.edit',
            $draft
        );
    }



}