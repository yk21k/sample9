<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\Shop;

class EnsureManager
{
    public function handle($request, Closure $next)
    {
        $user = auth()->user();
        $member = $request->attributes->get('shop_member');

        // 🔥 owner判定
        $shop = $request->route('shop');
        $isOwner = $shop
            ? Shop::where('id', $shop->id)
                ->where('user_id', $user->id)
                ->exists()
            : false;

        // 🔥 manager判定
        $isManager = $member && $member->role === 'manager';

        if (!$isOwner && !$isManager) {
            abort(403, 'オーナーとマネージャーのみ');
        }

        return $next($request);
    }
}
