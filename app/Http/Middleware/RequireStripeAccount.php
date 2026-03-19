<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RequireStripeAccount
{
    public function handle(Request $request, Closure $next)
    {
        $user = auth()->user();

        if (!$user || !$user->stripe_account_id) {

            // products一覧ページは除外
            if (
                !$request->is('admin/products') &&
                (
                    $request->is('admin/products/create') ||
                    $request->is('admin/product/import') ||
                    $request->is('admin/product/import/*')
                )
            ) {
                return redirect('/admin/products')
                    ->with('error','Stripe連携が必要です');
            }
        }

        return $next($request);
    }
}