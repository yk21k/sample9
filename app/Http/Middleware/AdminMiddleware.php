<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();
        if (!$user || !$user->hasRole('admin')) {
            abort(403, '管理者のみアクセス可能です');
        }

        return $next($request);


        // $user = Auth::user();

        // if (!$user || $user->role->name !== 'admin') {
        //     abort(403, '管理者のみアクセス可能です');
        // }

        // return $next($request);
        

        // if (!$user || $user->role_id != 1) {
        //     abort(403, '管理者のみアクセス可能です');
        // }
    }
}
