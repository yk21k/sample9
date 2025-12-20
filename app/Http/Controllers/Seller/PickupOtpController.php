<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\PickupOtp;
use App\Models\PickupOrder;
use Illuminate\Support\Facades\Auth;

class PickupOtpController extends Controller
{
    /**
     * OTP発行画面
     */
    public function showRequestForm($orderId)
    {
        $order = PickupOrder::findOrFail($orderId);
        return view('pickup.otp_request', compact('order'));
    }

    /**
     * OTPを発行
     */
    public function issue(Request $request)
    {
        $request->validate(['order_id' => 'required|integer']);
        $order = PickupOrder::findOrFail($request->order_id);

        $otp = PickupOtp::issue($order);

        // 実際はメール/SMS通知処理を入れる（ここでは画面表示）
        return redirect()->route('pickup.otp.verify.form', $order->id)
                         ->with('success', "OTPを発行しました（デモ用）: {$otp->otp_code}");
    }

    public function verifyByUser(Request $request)
    {
        $request->validate(['otp_code' => 'required|string|max:6']);

        $user = Auth::user();

        try {
            $result = DB::transaction(function () use ($request, $user) {
                // row-level lock: 同じコードに対する同時検証を防ぐ
                $otp = PickupOtp::where('otp_code', $request->otp_code)
                    ->whereNull('verified_at')     // 既に使われたものは対象外
                    ->lockForUpdate()
                    ->first();

                if (!$otp) {
                    return ['ok' => false, 'message' => '無効なコード、または既に使用されています。'];
                }

                if (now()->greaterThan($otp->expires_at)) {
                    return ['ok' => false, 'message' => 'このコードは有効期限切れです。'];
                }

                // （オプション） 発行者のみが使えるようにする場合はここでチェック
                if ($otp->user_id !== $user->id) {
                    return ['ok' => false, 'message' => 'このコードはあなたのものではありません。'];
                }

                // ここで確実に一回だけ書き換える（排他領域内）
                $otp->verified_at = now();
                $otp->verified_by_user_id = $user->id;
                $otp->save();

                // 注文状態更新（例）
                $order = $otp->order;
                $order->update(['status' => 'awaiting_handover']);

                return ['ok' => true, 'otp' => $otp, 'order' => $order];
            }, 5); // retry 5回（DBロック競合時）

            if (!$result['ok']) {
                return back()->withErrors(['otp_code' => $result['message']]);
            }

            return back()->with('success', '認証に成功しました。店舗が確認を行います。');
        } catch (\Exception $e) {
            \Log::error('OTP verify error: ' . $e->getMessage());
            return back()->withErrors(['otp_code' => 'システムエラーが発生しました。']);
        }
    }

}

