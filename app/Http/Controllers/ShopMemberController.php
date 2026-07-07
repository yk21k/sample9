<?php

namespace App\Http\Controllers;

use App\Models\Shop;
use App\Models\ShopMember;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Mail\StaffInviteMail;
use App\Models\InviteLog;
use App\Models\User;
use App\Helpers\Audit;

class ShopMemberController extends Controller
{
    // 一覧
    public function index(Shop $shop)
    {
        $this->authorize('manageStaff', $shop);

        $members = ShopMember::where('shop_id', $shop->id)
        ->withCount([
            'inviteLogs as invite_count'
        ])
        ->with([
            'inviteLogs' => function ($q) {
                $q->latest()->limit(1); // 最新1件
            }
        ])
        ->get();

        return view('sellers.shop_members.index', compact('shop', 'members'));
    }

    // 追加画面
    public function create(Shop $shop)
    {
        // dd('App\Http\Controllers\ShopMemberController');

        $this->authorize('manageStaff', $shop);

        // 🔥 既にこのshopに所属しているuser_idを取得
        $shop_members = ShopMember::where('shop_id', $shop->id)->get();
        // dd($shop_members);

        return view('sellers.shop_members.create', compact('shop', 'shop_members'));
    }

    // 🔥 招待付き保存
    public function store(Request $request, Shop $shop)
    {
        $this->authorize('manageStaff', $shop);

        Log::info('ShopMember store start', [
            'auth_user_id' => auth()->id(),
            'shop_id' => $shop->id,
            'input' => $request->only(['user_id','email','role']),
        ]);

        $data = $request->validate([
            'user_id' => 'nullable|exists:users,id',
            'name'  => 'nullable|string|max:255',
            'email' => 'nullable|email',
            'role'  => 'required|in:manager,staff',
        ]);

        // ========================================
        // 🔵 ① 既存ユーザー追加
        // ========================================
        if (!empty($data['user_id'])) {

            Log::info('Attach existing user attempt', [
                'shop_id' => $shop->id,
                'target_user_id' => $data['user_id'],
                'role' => $data['role'],
            ]);

            $exists = ShopMember::where('shop_id', $shop->id)
                ->where('user_id', $data['user_id'])
                ->exists();

            if ($exists) {
                Log::warning('Duplicate member attach', [
                    'shop_id' => $shop->id,
                    'user_id' => $data['user_id'],
                ]);

                return back()->withErrors('既に追加されています');
            }

            $member = ShopMember::create([
                'shop_id' => $shop->id,
                'user_id' => $data['user_id'],
                'role' => $data['role'],
            ]);

            Log::info('Existing user attached', [
                'shop_member_id' => $member->id,
            ]);

            return back()->with('success', '既存ユーザーを追加しました');
        }

        // ========================================
        // 🔴 ② 新規招待
        // ========================================

        if (empty($data['email'])) {
            return back()->withErrors('メールアドレスを入力してください');
        }

        // 重複チェック
        $exists = ShopMember::where('shop_id', $shop->id)
            ->where('email', $data['email'])
            ->exists();

        if ($exists) {
            Log::warning('Duplicate invite email', [
                'shop_id' => $shop->id,
                'email' => $data['email'],
            ]);

            return back()->withErrors('このメールは既に招待済みです');
        }

        // manager上限チェック
        if ($data['role'] === 'manager') {
            $count = ShopMember::where('shop_id', $shop->id)
                ->where('role', 'manager')
                ->count();

            if ($count >= 3) {
                Log::warning('Manager limit exceeded', [
                    'shop_id' => $shop->id,
                    'current_count' => $count,
                ]);

                return back()->withErrors('managerは最大3名までです');
            }
        }

        // 🔥 トークン生成（文字列化）
        $token = (string) Str::uuid();

        $member = ShopMember::create([
            'shop_id' => $shop->id,
            'name' => $data['name'],
            'email' => $data['email'],
            'role' => $data['role'],
            'invite_token' => $token,
            'invite_at' => now(),
        ]);

        Log::info('Invite member created', [
            'shop_member_id' => $member->id,
            'email' => $member->email,
            'role' => $member->role,
            'token' => $token,
        ]);

        $url = route('invite.staff.register.form', [
            'token' => $token
        ]);

        // ========================================
        // 📩 メール送信
        // ========================================
        try {
            Mail::to($member->email)
                ->send(new StaffInviteMail($member, $url));

            Log::info('Invite mail sent', [
                'email' => $member->email,
            ]);

            $status = 'sent';

        } catch (\Exception $e) {

            Log::error('Mail send failed', [
                'email' => $member->email,
                'error' => $e->getMessage(),
            ]);

            $status = 'failed';
        }

        // ========================================
        // 🧾 InviteLog（DBログ）
        // ========================================
        InviteLog::create([
            'shop_member_id' => $member->id,
            'email' => $member->email,
            'token' => $token,
            'status' => $status,
            'sent_at' => now(),
        ]);

        Log::info('InviteLog created', [
            'shop_member_id' => $member->id,
            'status' => $status,
        ]);

        // =========================
        // 🔥 Audit Log
        // =========================
        Audit::log(
            'member.invited',
            $member,
            null,
            $member->toArray(),
            'メンバー招待送信'
        );




        if ($status === 'failed') {
            return back()->withErrors('メール送信に失敗しました');
        }

        return redirect()
            ->route('shop_members.index', $shop)
            ->with('success', '招待メールを送信しました');
    }

    public function attach(Request $request, Shop $shop)
    {
        $data = $request->validate([
            'user_id' => 'required|exists:users,id',
            'role' => 'required|in:manager,staff',
        ]);

        // 重複チェック
        $exists = ShopMember::where('shop_id', $shop->id)
            ->where('user_id', $data['user_id'])
            ->exists();

        if ($exists) {
            return back()->withErrors('既に追加されています');
        }

        ShopMember::create([
            'shop_id' => $shop->id,
            'user_id' => $data['user_id'],
            'role' => $data['role'],
        ]);

        return back()->with('success', '追加しました');
    }

    public function destroy(ShopMember $member)
    {
        // ========================================
        // 🔥 ログイン中member
        // ========================================
        $authMember = ShopMember::where(
                'user_id',
                auth()->id()
            )
            ->where(
                'shop_id',
                $member->shop_id
            )
            ->first();

        // ========================================
        // 🔥 権限
        // ========================================
        if (
            !$authMember ||
            !in_array(
                $authMember->role,
                ['owner', 'manager']
            )
        ) {

            abort(403, '権限がありません');

        }

        // ========================================
        // 🔥 既に削除済み
        // ========================================
        if ($member->trashed()) {

            return back()->withErrors(
                '既に削除済みです'
            );

        }

        // ========================================
        // 🔥 owner削除禁止
        // ========================================
        if ($member->role === 'owner') {

            return back()->withErrors(
                'ownerは削除できません'
            );

        }

        // ========================================
        // 🔥 managerはmanager削除不可
        // ========================================
        if (
            $authMember->role === 'manager' &&
            $member->role === 'manager'
        ) {

            return back()->withErrors(
                'managerは他managerを削除できません'
            );

        }

        // ========================================
        // 🔥 自分削除禁止
        // ========================================
        if (
            $member->user_id === auth()->id()
        ) {

            return back()->withErrors(
                '自分自身は削除できません'
            );

        }

        if ($member->role === 'manager') {

            // ========================================
            // 🔥 shop再審査化
            // ========================================
            $member->shop->update([

                'review_status' => 're_review',

            ]);

            // ========================================
            // 🔥 審査申請生成
            // ========================================
            ShopApplication::create([

                'shop_id' => $member->shop_id,

                'user_id' => auth()->id(),

                'type' => 'manager_changed',

                'status' => 'pending',

                'after_data' => [
                    'deleted_manager_id' => $member->id,
                ],

            ]);

        }

        // ========================================
        // 🔥 audit before
        // ========================================
        $before = $member->toArray();

        // ========================================
        // 🔥 soft delete
        // ========================================
        $member->delete();

        // ========================================
        // 🔥 audit log
        // ========================================
        \App\Helpers\Audit::log(

            'member.deleted',

            $member,

            $before,

            null,

            'メンバー削除'

        );

        return back()->with(
            'success',
            'メンバーを削除しました'
        );
    }

    public function resend(ShopMember $member)
    {
        $this->authorize('manageStaff', $member->shop);

        // ========================================
        // 🔥 1分以内再送禁止
        // ========================================
        if (
            $member->invite_at &&
            $member->invite_at->gt(now()->subMinute())
        ) {

            return back()->withErrors(
                '1分以内は再送できません'
            );
        }

        // ========================================
        // 🔥 token再生成
        // ========================================
        $token = (string) \Str::uuid();

        $member->update([
            'invite_token' => $token,
            'invite_at' => now(),
        ]);

        // ========================================
        // 🔥 招待URL
        // ========================================
        $url = route('invite.staff.register.form', [
            'token' => $member->invite_token
        ]);

        // ========================================
        // 🔥 mail送信
        // ========================================
        Mail::to(
            $member->email ?? optional($member->user)->email
        )->send(
            new StaffInviteMail($member, $url)
        );

        // ========================================
        // 🔥 invite log
        // ========================================
        InviteLog::create([
            'shop_member_id' => $member->id,
            'email' => $member->email,
            'token' => $token,
            'status' => 'resent',
            'sent_at' => now(),
        ]);

        // ========================================
        // 🔥 audit log
        // ========================================
        \App\Helpers\Audit::log(
            'member.resent',
            $member,
            null,
            $member->toArray(),
            'メンバー再招待'
        );

        return back()->with(
            'success',
            '再招待しました'
        );
    }

    public function expire(ShopMember $member)
    {
        $this->authorize('manageStaff', $member->shop);

        $member->update([
            'invite_token' => null,
        ]);

        InviteLog::create([
            'shop_member_id' => $member->id,
            'email' => $member->email,
            'status' => 'expired',
            'sent_at' => now(),
        ]);

        return back()->with('success', '招待を失効しました');
    }

    public function logs(ShopMember $member)
    {
        $this->authorize('manageStaff', $member->shop);

        $logs = InviteLog::where('shop_member_id', $member->id)
            ->latest()
            ->get();

        return view('sellers.shop_members.logs', compact('member', 'logs'));
    }

    public function setEmail(Request $request, $id)
    {
        $member = ShopMember::findOrFail($id);

        // 🔥 権限チェック（超重要）
        $authMember = \App\Models\ShopMember::where('user_id', auth()->id())
            ->where('shop_id', $member->shop_id)
            ->first();

        if (!$authMember || !in_array($authMember->role, ['owner','manager'])) {
            abort(403, '管理権限がありません');
        }

        $data = $request->validate([
            'email' => 'required|email',
        ]);

        // 🔥 usersに既に存在するかチェック
        if (User::where('email', $data['email'])->exists()) {
            return back()->withErrors('このメールアドレスは既に登録されています');
        }

        // 🔥 同一店舗内の重複チェック
        $exists = ShopMember::where('shop_id', $member->shop_id)
            ->where('email', $data['email'])
            ->where('id', '!=', $member->id)
            ->exists();

        if ($exists) {
            return back()->withErrors('この店舗内で既に使用されています');
        }

        $member->update([
            'email' => $data['email']
        ]);

        return back()->with('success', 'メールを登録しました');
    }

    public function invite($id)
    {
        $member = ShopMember::findOrFail($id);

        if (!$member->email) {
            return back()->withErrors('メール未設定');
        }

        $token = (string) \Str::uuid();

        $member->update([
            'invite_token' => $token,
            'invite_at' => now(),
            'invite_expires_at' => now()->addDays(2),
        ]);

        $url = route('invite.staff.register.form', [
            'token' => $token
        ]);

        Mail::to($member->email)
            ->send(new StaffInviteMail($member, $url));

        return back()->with('success', '招待メール送信');
    }

    public function show($token)
    {
        $member = ShopMember::where('invite_token', $token)
            ->firstOrFail();

        // 🔥 登録済み
        if ($member->user_id) {
            abort(403, '既に登録済みです');
        }

        // 🔥 期限切れ
        if (
            $member->invite_expires_at &&
            now()->gt($member->invite_expires_at)
        ) {
            abort(403, '招待URLの期限が切れています');
        }

        return view(
            'sellers.shop_members.staff.register',
            compact('member')
        );
    }

}
