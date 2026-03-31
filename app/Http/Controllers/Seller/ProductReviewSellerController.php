<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use App\Models\Product;
use App\Models\ProductReviewQueue;


class ProductReviewSellerController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        $products = Product::where('shop_id', $user->shop->id)->get();

        $approved_products = Product::where('shop_id', $user->shop->id)->where('review_status', 'approved')->get();

        $todayCount = ProductReviewQueue::where('user_id', $user->id)
            ->whereDate('requested_at', today())
            ->count();

        return view('sellers.product_review.dashboard', compact(
            'products',
            'todayCount',
            'approved_products'
        ));
    }

    public function fixForm(Product $product)
    {
        $queue = ProductReviewQueue::where('product_id', $product->id)->first();

        $product->load('reviewQueue');

        $fixFields = [];

        $fixFields = $queue->fix_fields ?? [];

        return view('sellers.product_review.fix', compact(
            'product',
            'queue',
            'fixFields'
        ));
    }
}
