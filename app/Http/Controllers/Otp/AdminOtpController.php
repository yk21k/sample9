<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\OtpToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminOtpController extends Controller
{
    /**
     * OTP入力画面
     */
    public function showForm()
    {
        return view('admin_otp.form');
    }

    /**
     * OTP送信（発行）
     */
    public function send()
    {
        $user = Auth::user();
        $code = rand(100000, 999999);

        // 既存の未使用OTP削除
        OtpToken::where('user_id', $user->id)
            ->whereNull('verified_at')
            ->delete();

        // 新規発行
        OtpToken::create([
            'user_id' => $user->id,
            'code' => $code,
            'expires_at' => now()->addMinutes(10),
        ]);

        // ここでメール送信など書ける
        // Mail::to($user->email)->send(new SendOtpMail($code));

        return back()->with('success', 'OTP を送信しました（デモのため画面に表示します）: ' . $code);
    }

    /**
     * OTP検証
     */
    public function verify(Request $request)
    {
        $request->validate([
            'otp' => 'required|digits:6',
        ]);

        $otp = OtpToken::where('user_id', Auth::id())
            ->where('code', $request->otp)
            ->whereNull('verified_at')
            ->first();

        if (!$otp) {
            return back()->withErrors(['otp' => 'OTP が一致しません']);
        }

        if ($otp->expires_at->isPast()) {
            return back()->withErrors(['otp' => 'OTP の有効期限が切れています']);
        }

        // 確認成功
        $otp->update(['verified_at' => now()]);

        // セッションに「OTP 通過」を記録
        session(['otp_verified' => true]);

        return redirect()->intended('/dashboard')->with('success', 'OTP 認証に成功しました');
    }
}
