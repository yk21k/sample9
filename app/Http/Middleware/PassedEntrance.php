<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class PassedEntrance
{
    public function handle(Request $request, Closure $next)
    {
        // 入口ページ自体は常に通す（無限ループ防止）
        if ($request->routeIs('entrance')) {
            return $next($request);
        }

        // Cookie があれば通す
        if ($request->cookie('passed_entrance')) {
            return $next($request);
        }

        // それ以外は入口へ
        return redirect()->route('entrance');
    }
}
