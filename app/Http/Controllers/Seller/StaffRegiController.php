<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;

use App\Models\User;
use App\Models\ShopMember;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class StaffRegiController extends Controller
{
    public function showForm(Request $request)
    {
        logger()->info([
            'full_url' => $request->fullUrl(),
            'route_params' => $request->route()->parameters(),
        ]);
        
        $member = ShopMember::where('invite_token', $request->token)->firstOrFail();

        // 🔥 期限チェック（3日）
        if (!$member->invited_at || $member->invited_at->addDays(3)->isPast()) {
            abort(403, '招待リンクの有効期限が切れています');
        }

        // 🔥 すでに登録済み
        if ($member->user_id) {
            abort(403, '既に登録済みです');
        }

        return view('shop_staff.auth.staff_register', compact('member'));
    }

    public function register(Request $request)
    {
        $member = ShopMember::where('invite_token', $request->token)->firstOrFail();

        // 🔥 バリデーション
        $request->validate([
            'password' => 'required|min:8|confirmed',
        ]);

        // 🔥 期限チェック
        if (!$member->invite_at || $member->invited_at->addDays(3)->isPast()) {
            abort(403, '招待リンクの有効期限が切れています');
        }

        // 🔥 二重登録防止
        if ($member->user_id) {
            abort(403, '既に登録済みです');
        }

        // 🔥 メール重複チェック
        if (User::where('email', $member->email)->exists()) {
            abort(403, 'このメールアドレスは既に使用されています');
        }

        DB::transaction(function () use ($request, $member, &$user) {

            // 🔥 User作成
            $user = User::create([
                'name' => $member->name,
                'email' => $member->email,
                'password' => Hash::make($request->password),
                'role_id' => 3,
                'can_purchase' => false,
            ]);

            // 🔥 紐付け & トークン無効化
            $member->update([
                'user_id' => $user->id,
                'invite_token' => null,
            ]);

            // 🔥 ★ここ追加（ログ）
            \App\Models\InviteLog::create([
                'shop_member_id' => $member->id,
                'email' => $member->email,
                'status' => 'registered',
                'sent_at' => now(),
            ]);
        });

        // 🔥 ログイン
        Auth::login($user);

        return redirect('/seller')->with('success', 'スタッフ登録が完了しました');
    }
}