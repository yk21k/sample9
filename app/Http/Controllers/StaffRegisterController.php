<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class StaffRegisterController extends Controller
{
    public function show($token)
    {
        $member = \App\Models\ShopMember::where('invite_token', $token)->firstOrFail();

        if (!$member) {
            dd('token not found', $token);
        }

        return view('sellers.shop_members.staff.register', compact('member'));
    }

    // public function register(Request $request, $token)
    // {
    //     $member = \App\Models\ShopMember::where('invite_token', $token)->firstOrFail();

    //     $data = $request->validate([
    //         'email' => 'required|email',
    //         'password' => 'required|min:8|confirmed',
    //     ]);

    //     // 🔥 user作成
    //     $user = \App\Models\User::create([
    //         'name' => $member->name,
    //         'email' => $data['email'],
    //         'password' => bcrypt($data['password']),
    //     ]);

    //     // 🔥 紐付け
    //     $member->update([
    //         'user_id' => $user->id,
    //         'invite_token' => null,
    //     ]);

    //     auth()->login($user);

    //     return redirect('/seller');
    // }

    public function register(Request $request, $token)
    {
        $member = \App\Models\ShopMember::where('invite_token', $token)
            ->firstOrFail();

        // ========================================
        // 🔥 登録済みチェック
        // ========================================
        if ($member->user_id) {
            abort(403, '既に登録済みです');
        }

        // ========================================
        // 🔥 招待期限チェック
        // ========================================
        if (
            $member->invite_expires_at &&
            now()->gt($member->invite_expires_at)
        ) {
            abort(403, '招待URLの期限が切れています');
        }

        // ========================================
        // 🔥 バリデーション
        // ========================================
        $data = $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:8|confirmed',
        ]);

        // ========================================
        // 🔥 email一致チェック（重要）
        // ========================================
        if (
            $member->email &&
            strtolower($member->email) !== strtolower($data['email'])
        ) {
            return back()->withErrors(
                '招待されたメールアドレスと一致しません'
            );
        }

        // ========================================
        // 🔥 users重複チェック
        // ========================================
        $exists = \App\Models\User::where('email', $data['email'])
            ->exists();

        if ($exists) {
            return back()->withErrors(
                'このメールアドレスは既に登録されています'
            );
        }

        // ========================================
        // 🔥 user作成
        // ========================================
        $user = \App\Models\User::create([
            'name' => $member->name,
            'email' => strtolower($data['email']),
            'password' => bcrypt($data['password']),
        ]);

        // ========================================
        // 🔥 ShopMember紐付け
        // ========================================
        $member->update([
            'user_id' => $user->id,
            'invite_token' => null,
            'invite_expires_at' => null,
        ]);

        // ========================================
        // 🔥 ログイン
        // ========================================
        auth()->login($user);

        return redirect('/seller');
    }

}
