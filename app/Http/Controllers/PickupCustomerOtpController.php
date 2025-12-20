<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PickupOtp;
use App\Models\PickupSecondaryOtp;
use App\Models\PickupOrder;
use App\Models\PickupOrderItem;
use Carbon\Carbon;
use Auth;
use Illuminate\Support\Str;
use App\Mail\PickupOtpMail;
use Illuminate\Support\Facades\Mail;


class PickupCustomerOtpController extends Controller
{
    /**
     * 購入者の注文一覧（OTP発行＋受取確認）
     */
    public function index()
    {
        $orders = PickupOrder::with(['items', 'otp'])
            ->where('user_id', Auth::id())

            // ▼ ステータスが received 以外の item を持つ注文だけを取得
            ->whereHas('items', function ($query) {
                $query->where('status', '!=', 'received');
            })

            ->orderByDesc('created_at')
            ->get();

        return view('otp.otp_request', compact('orders'));
    }

    /**
     * OTP発行処理
     */
    public function generate(Request $request)
    {
        $request->validate([
            'order_id' => 'required|exists:pickup_orders,id',
        ]);

        $user = auth()->user();
        $order = PickupOrder::where('user_id', $user->id)->findOrFail($request->order_id);

        // 既存OTP確認
        $existingOtp = PickupOtp::where('order_id', $order->id)
            ->where('user_id', $user->id)
            ->where('status', 'unused')
            ->where('expires_at', '>', now())
            ->latest()
            ->first();

        if ($existingOtp) {
            return back()->with([
                'otp_code' => $existingOtp->code,
                'otp_expires_at' => $existingOtp->expires_at->format('Y/m/d H:i'),
                'info' => '有効なOTPがすでに存在します。',
            ]);
        }

        // 新規発行
        $otp = PickupOtp::create([
            'user_id' => $user->id,
            'order_id' => $order->id,
            'code' => rand(100000, 999999),
            'expires_at' => now()->addMinutes(10),
            'status' => 'unused',
        ]);

        // ▼ メール送信処理を追加
        Mail::to($user->email)->send(
            new PickupOtpMail(
                $otp->code,
                Carbon::parse($otp->expires_at)->format('Y/m/d H:i')
            )
        );

        return back()->with([
            'otp_code' => $otp->code,
            'otp_expires_at' => Carbon::parse($otp->expires_at)->format('Y/m/d H:i'),
            'success' => '新しいワンタイムパスワードを発行しました。',
        ]);
    }


    /**
     * 🧾 OTP入力フォームを表示（購入者が再ログインする画面）
     */
    public function showLoginForm()
    {
        return view('otp.otp_login');
    }

    /**
     * ✅ OTPログイン処理
     * （購入者が発行済みOTPで再ログインする）
     */
    public function verifyOtp(Request $request)
    {
        $request->validate([
            'code' => 'required|digits:6',
        ]);

        $user = auth()->user();

        $otp = PickupOtp::where('code', $request->code)
            ->where('user_id', $user->id)
            ->where('status', 'unused')
            ->where('expires_at', '>', now())
            ->first();

        if (!$otp) {
            return back()->with('error', '無効または期限切れのOTPです。');
        }

        // OTPを使用済みに変更 
        $otp->update([
            'status' => 'used',
            'used_at' => now(),
        ]);
        
        // ✅ 店舗提示用OTP（第2段階）を生成
        $secondaryOtp = PickupSecondaryOtp::create([
            'pickup_otp_id' => $otp->id,
            'order_id' => $otp->order_id,
            'user_id' => $user->id, // ← ★これを追加！
            'code' => strtoupper(Str::random(6)), // 例: A3B9X7K2
            'expires_at' => now()->addMinutes(30),
            'status' => 'unused',
        ]);

        // セッションに認証済みOTP情報を記録
        session(['verified_otp_id' => $otp->id]);

        return redirect()->route('pickup.otp.secure.show', ['otp' => $secondaryOtp->id]);
    }

    public function logoutOtp(Request $request)
    {
        $user = auth()->user();

        // セッションに保存されている OTP 情報を取得
        $verifiedOtpId = session('verified_otp_id');

        if (!$verifiedOtpId) {
            return back()->with('error', 'ログイン中のOTP情報が見つかりません。');
        }

        $otp = PickupOtp::where('id', $verifiedOtpId)
            ->where('user_id', $user->id)
            ->first();

        if (!$otp) {
            return back()->with('error', '対象のOTPが見つかりません。');
        }

        // ✅ OTPを再び使用可能に戻す
        $otp->update([
            'status' => 'unused',
            'used_at' => null,
        ]);

        // ✅ セッションをクリア
        session()->forget('verified_otp_id');

        return redirect()->route('pickup.otp.login.form')->with('info', 'OTPログインを解除しました。再度ログインできます。');
    }

    /**
     * ✅ OTP認証後の安全ページ
     * （本人だけがアクセス可能）
     */
    public function showSecurePage(PickupSecondaryOtp $otp)
    {
        // セッションチェック（他人アクセス防止）
        if (session('verified_otp_id') !== $otp->pickup_otp_id) {
            abort(403, 'このページにはアクセスできません');
        }

        // OTPが期限切れなら失効処理
        if ($otp->expires_at->isPast() && $otp->status === 'unused') {
            $otp->update(['status' => 'expired']);
        }

        return view('otp.secure_page', compact('otp'));
    }

    /**
     * 商品単位の受取確認
     */
    public function confirmItem(Request $request, $id)
    {
        $item = PickupOrderItem::findOrFail($id);

        // 自分の注文であるか確認
        if ($item->order->user_id !== Auth::id()) {
            abort(403, 'この商品の受取確認はできません。');
        }

        // ステータス更新
        $item->update([
            'status' => 'received',
            'received_at' => now(),
        ]);

        return back()->with('success', "{$item->product->name} の受取を確認しました。");
    }

}
