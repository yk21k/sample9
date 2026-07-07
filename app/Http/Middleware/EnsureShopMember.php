<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\ShopMember;
use App\Models\Shop;

class EnsureShopMember
{
    public function handle(Request $request, Closure $next)
    {
        $user = auth()->user();

        if (!$user) {
            abort(403, 'ユーザーではありません');
        }

        $member = $request->route('member');

        if ($member) {
            $shopId = $member->shop_id;
        } else {
            $shop = $request->route('shop');
            $shopId = $shop instanceof Shop ? $shop->id : $shop;
        }

        // dd($request->route()->parameters());
        // if (!$shopId) {
        //     abort(403, 'shopが特定できません');
        // }

        $shop = $request->route('shop');

        if (!$shop) {
            return $next($request); // 🔥これ
        }

        $authMember = ShopMember::where('user_id', $user->id)
            ->where('shop_id', $shopId)
            ->first();

        $owner = Shop::where('id', $shopId)
            ->where('user_id', $user->id)
            ->exists();

        if (!$authMember && !$owner) {
            abort(403, '店舗に所属していません');
        }

        $request->attributes->set('shop_member', $authMember);

        return $next($request);
    }
}
