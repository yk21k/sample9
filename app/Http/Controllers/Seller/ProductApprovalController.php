<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use App\Services\ProductService;

class ProductApprovalController extends Controller
{
    protected $service;

    public function __construct(ProductService $service)
    {
        $this->service = $service;
    }

    /**
     * 🔍 審査画面（差分表示）
     */
    public function show(Product $product)
    {
        $this->authorize('approveManager', $product);

        // 🔥 最新の差分ログ（staff更新）
        $log = ActivityLog::where('target_type', 'Product')
            ->where('target_id', $product->id)
            ->where('action', 'seller_updated')
            ->latest()
            ->first();

        return view('seller.products.review', compact('product', 'log'));
    }

    /**
     * ✅ 承認（manager → pending）
     */
    public function approve(Request $request, Product $product)
    {
        $this->authorize('approveManager', $product);

        $this->service->approveByManager(
            $product,
            auth()->user(),
            $request->input('comment')
        );

        return redirect()
            ->route('seller.products.review', $product)
            ->with('success', '審査へ送信しました');
    }

    /**
     * ❌ 差し戻し
     */
    public function reject(Request $request, Product $product)
    {
        $this->authorize('approveManager', $product);

        $request->validate([
            'comment' => 'required|string|max:1000',
        ]);

        $this->service->rejectByManager(
            $product,
            auth()->user(),
            $request->input('comment')
        );

        return redirect()
            ->route('seller.products.review', $product)
            ->with('error', '差し戻しました');
    }
}