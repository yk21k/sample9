<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequireOtp
{
    public function handle(Request $request, Closure $next)
    {
        // すでに OTP 認証済みなら通過
        if (session('otp_verified') === true) {
            return $next($request);
        }

        // OTP 未認証 → OTP 入力ページへリダイレクト
        return redirect()->route('otp.form')->with('warning', 'このページにアクセスするにはOTP認証が必要です。');
    }
}
