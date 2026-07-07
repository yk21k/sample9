<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureOwner
{
    public function handle($request, Closure $next)
    {
        $member = $request->attributes->get('shop_member');

        if (!$member || !$member->isOwner()) {
            abort(403, 'ownerのみ');
        }

        return $next($request);
    }
}
