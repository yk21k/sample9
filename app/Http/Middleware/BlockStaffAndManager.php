<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\ShopMember;

class BlockStaffAndManager
{
    public function handle(Request $request, Closure $next)
    {
        $user = auth()->user();

        if (!$user) {
            abort(403);
        }

        $member = ShopMember::where('user_id', $user->id)->first();

        // 🔥 staff / manager を弾く
        if ($member && in_array($member->role, ['staff', 'manager'])) {
            abort(403, 'このページは利用できません');
        }

        return $next($request);
    }
}
