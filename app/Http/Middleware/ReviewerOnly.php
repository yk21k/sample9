<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ReviewerOnly
{
    public function handle(Request $request, Closure $next)
    {
        $user = auth()->user();

        if (!$user || !in_array($user->role_id, [1, 5])) {
            abort(403, '権限がありません');
        }

        return $next($request);
    }
}
