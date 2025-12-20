<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

use App\Models\ShopStaff;
use App\Models\DailyQrCode;
use Illuminate\Support\Facades\Hash;
use Auth;

class StaffRegisterController extends Controller
{
    public function create()
    {
        $today = now()->toDateString();

        // 今日のQRを取得、なければ作成
        $qrData = DailyQrCode::firstOrCreate(
            ['date' => $today],
            ['token' => Str::random(20)]
        );

        $token = $qrData->token;

        return view('sellers.shop_staff.register', compact('token'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:50',
            'email' => 'required|email|unique:shop_staff,email',
            'password' => 'required|min:8|confirmed',
        ]);

        ShopStaff::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'shop_id'      => auth()->user()->shop->id, // ← 所属店舗のIDを自動付与
            'active_until' => now()->addMonth(),
        ]);

        return redirect()->route('shop_staff.login')->with('status', '登録が完了しました。');
    }



}
