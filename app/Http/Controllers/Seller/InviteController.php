<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller; 
use App\Models\ShopMember;
use App\Models\InviteLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class InviteController extends Controller
{
    /**
     * 一覧
     */
    public function index()
    {
        $shop = auth()->user()->shop;

        $members = ShopMember::where('shop_id', $shop->id)
            ->latest()
            ->get();

        return view('seller.invites.index', compact('members'));
    }

    /**
     * 作成画面
     */
    public function create()
    {
        return view('seller.invites.create');
    }

    /**
     * 招待送信
     */
    public function store(Request $request)
    {
        $shop = auth()->user()->shop;

        $request->validate([
            'name' => 'required',
            'email' => 'required|email',
        ]);

        $member = ShopMember::create([
            'shop_id' => $shop->id,
            'name' => $request->name,
            'email' => $request->email,
            'role' => 'staff',
        ]);

        $this->sendInvite($member, 'sent');

        return redirect()->route('invite.index')->with('success', '招待送信しました');
    }

    /**
     * 再招待
     */
    public function reInvite($id)
    {
        $member = ShopMember::findOrFail($id);

        $this->sendInvite($member, 'resent');

        return back()->with('success', '再送しました');
    }

    /**
     * 失効
     */
    public function expire($id)
    {
        $member = ShopMember::findOrFail($id);

        $member->update([
            'invite_token' => null,
        ]);

        InviteLog::create([
            'shop_member_id' => $member->id,
            'email' => $member->email,
            'token' => null,
            'status' => 'expired',
            'sent_at' => now(),
        ]);

        return back()->with('success', '失効しました');
    }

    /**
     * ログ一覧
     */
    public function logs()
    {
        $logs = InviteLog::latest()->paginate(20);

        return view('seller.invites.logs', compact('logs'));
    }

    /**
     * 共通：招待送信
     */
    private function sendInvite($member, $type = 'sent')
    {
        $token = Str::random(64);

        $member->update([
            'invite_token' => $token,
            'invited_at' => now(),
        ]);

        // メール送信
        
        Mail::to($member->email)->send(new StaffInviteMail($token));

        // ログ
        InviteLog::create([
            'shop_member_id' => $member->id,
            'email' => $member->email,
            'token' => $token,
            'status' => $type,
            'sent_at' => now(),
        ]);
    }
}
