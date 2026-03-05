<?php

namespace App\Http\Controllers\Admin;

use App\Models\Product;
use Illuminate\Http\Request;
use TCG\Voyager\Http\Controllers\VoyagerBaseController;

class ProductReviewController extends VoyagerBaseController
{

    public function index(Request $request)
    {
        $products = Product::where('status',1)
            ->latest()
            ->paginate(20);

        return view('admin.product_review.index',compact('products'));
    }

    public function approve(Product $product)
    {
        $product->update([
            'status' => 2,
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now()
        ]);

        return back()->with('success','商品を承認しました');
    }

    public function reject(Request $request, Product $product)
    {
        $product->update([
            'status' => 3,
            'review_comment' => $request->review_comment,
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now()
        ]);

        return back()->with('error','商品を否認しました');
    }

}
