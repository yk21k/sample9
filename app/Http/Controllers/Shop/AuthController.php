<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\PickupSecondaryOtp;
use App\Models\PickupOrder;
use App\Models\PickupOrderItem;
use App\Models\DailyQrCode;
use App\Mail\StorePickupCompletedMail;
use Illuminate\Support\Facades\Mail;


class AuthController extends Controller
{
    public function showLoginForm($token)
    {
        $today = now()->toDateString();

        // 今日の token を取得
        $qrData = DailyQrCode::where('date', $today)->first();

        // 今日のQRが無い、または token が違う → アクセス拒否
        if (!$qrData || $qrData->token !== $token) {
            abort(403, 'このQRコードは無効です（期限切れの可能性があります）');
        }
        return view('shop_staff.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $credentials = $request->only('email', 'password');

        if (Auth::guard('shop_staff')->attempt($credentials)) {
            $staff = Auth::guard('shop_staff')->user();

            // 有効期限チェック
            if (!$staff->isActive()) {
                Auth::guard('shop_staff')->logout();
                return back()->withErrors(['expired' => 'このアカウントの有効期限が切れています。']);
            }

            return redirect()->route('shop.dashboard');
        }

        return back()->withErrors(['login' => 'メールアドレスまたはパスワードが違います。']);
    }

    public function logout()
    {
        Auth::guard('shop_staff')->logout();
        return redirect()->route('shop.login')->with('status', 'ログアウトしました。');
    }

    /**
     * スタッフ用ダッシュボード
     */
    public function dashboard()
    {
        return view('shop_staff.dashboard');
    }

    /**
     * OTPコードの照合処理
     */
    public function verifyOtpStaff(Request $request)
    {
        $request->validate([
            'otp_code' => [
                'required',
                'regex:/^[A-Z0-9]{6}$/', // 大文字英数字6桁
            ],
        ], [
            'otp_code.required' => 'OTPコードを入力してください。',
            'otp_code.regex' => 'OTPコードは英数字6文字で入力してください。',
        ]);

        $otp = PickupSecondaryOtp::where('code', $request->otp_code)
            ->where('status', 'unused')
            ->where('expires_at', '>', now())
            ->first();

        if (!$otp) {
            return back()->withErrors(['otp_code' => '使用済または期限切れのOTPです。']);
        }

         // dd($otp->order_id);
        // 該当注文を取得

        // 🔍 注文と関係データを一括ロード
        $order = PickupOrder::with([
            'items.product.shop',
            'reservations.slot',
            'items.slot',
        ])->find($otp->order_id);

        if (!$order) {
            return back()->withErrors(['otp_code' => '該当する注文が見つかりません。']);
        }

        // OTPを使用済みに更新 店員さんが受け渡した後に変更する。
        // $otp->update(['status' => 'used']);

        return view('shop_staff.dashboard', [
            'order' => $order,
            'verified' => true,
        ]);
    }

    public function personInCharge($itemId)
    {
        $staff = auth()->user();  // ログイン中の店員

        // 該当アイテムを取得
        $item = PickupOrderItem::findOrFail($itemId);

        // 状態が pending（受渡し前）以外は更新させない
        if ($item->status !== 'pending') {
            return back()->with('error', 'この注文はすでに処理済みか入金依頼可能と報告が必要です。');
        }

        // person_in_charge 更新
        $item->update([
            'status' => 'picked_up',
            'person_in_charge' => auth()->guard('shop_staff')->user()->name,
            'respose_time' => now(),
        ]);

        // ダッシュボードへリダイレクト
        return redirect()->route('shop.dashboard')->with('success', '受け渡し完了として記録しました。');
    }


    /**
     * 受渡し完了処理　未確認だが管理画面で利用中？
     */
    public function sendPickupConfirmation(PickupOrderItem $item)
    {
        $order = $item->order;
        $user = $order->user;

        // すでに送信済みなら再送信させない
        if ($item->pickup_mail_sent_at) {
            return back()->with('error', 'この商品はすでに受け渡し完了メールが送信されています。');
        }

        // ステータス更新
        $item->status = 'pending_confirmation';

        // メール送信
        Mail::to($user->email)->send(new StorePickupCompletedMail($item));

        // 送信日時を保存（再送信防止に必要）
        $item->pickup_mail_sent_at = now();
        $item->save();

        return back()->with('status', '受け渡し完了メールを送信しました。');
    }

    // 購入者宛のメール本文に利用
    public function showForm($token)
    {
        $item = PickupOrderItem::where('confirmation_token', $token)->firstOrFail();

        return view('pickup.confirm_form', compact('item'));
    }

    // 購入者宛のメール本文に利用
    public function submit(Request $request)
    {
        $item = PickupOrderItem::where('confirmation_token', $request->token)->firstOrFail();

        $item->update([
            'buyer_confirmed_at' => now(),
        ]);

        return redirect()->route('pickup.confirm.complete');
    }
}
