<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\ShopMember;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;


class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $shopId = $user->shop->id;

        $userIds = ShopMember::where('shop_id', $shopId)
            ->pluck('user_id');

        $query = ActivityLog::whereIn('user_id', $userIds);
        
        // 🔍 ユーザー
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // 🔍 アクション
        if ($request->filled('action')) {
            $query->where('action', 'like', '%' . $request->action . '%');
        }

        // 🔍 対象タイプ（Productなど）
        if ($request->filled('target_type')) {
            $query->where('target_type', $request->target_type);
        }

        // 🔍 対象ID
        if ($request->filled('target_id')) {
            $query->where('target_id', $request->target_id);
        }

        // 🔍 日付（from）
        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->from);
        }

        // 🔍 日付（to）
        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->to);
        }    

        // $logs = ActivityLog::whereIn('user_id', $userIds)
        //     ->latest()
        //     ->paginate(20);

        $logs = $query
        ->latest()
        ->paginate(20);

        return view('sellers.activity_logs.index', compact('logs'));
    }

    public function show($id)
    {
        $log = ActivityLog::findOrFail($id);
        $user = Auth::user();
        $shopId = $user->shop->id;

        $isSameShop = ShopMember::where('shop_id', $shopId)
            ->where('user_id', $log->user_id)
            ->exists();

        if (!$isSameShop) {
            abort(403);
        }

        return view('sellers.activity_logs.show', compact('log'));
    }

    public function productTimeline(Product $product)
    {
        $user = auth()->user();

        $shop = $user->currentShop();

        if (
            !$shop ||
            $product->shop_id !== $shop->id
        ) {
            abort(403);
        }

        $logs = ActivityLog::with('user')

            ->where('product_id', $product->id)

            ->latest()

            ->paginate(50);

        return view(
            'sellers.activity_logs.product_timeline',
            compact(
                'product',
                'logs'
            )
        );
    }
}
