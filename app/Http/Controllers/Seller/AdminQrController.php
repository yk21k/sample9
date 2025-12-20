<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\DailyQrCode;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use SimpleSoftwareIO\QrCode\Facades\QrCode;


class AdminQrController extends Controller
{
    /**
     * 今日のQRコード表示（なければ自動生成）
     */
    public function show()
    {
        $today = now()->toDateString();

        // 今日のQRを取得、なければ作成
        $qrData = DailyQrCode::firstOrCreate(
            ['date' => $today],
            ['token' => Str::random(40)] // 20 → 40でより安全に
        );

        // QRコードのURLを route で生成
        $loginUrl = route('shop_staff.login', ['token' => $qrData->token]);

        // QRコード生成
        $qr = QrCode::size(200)->generate($loginUrl);

        // 表示用の有効期限（実際はDB保持しない）
        $expiresAt = now()->endOfDay()->format('Y-m-d H:i');

        return view('sellers.qr.index', [
            'qr'        => $qr,
            'qrData'    => $qrData,
            'expiresAt' => $expiresAt,
            'token'     => $qrData->token,
        ]);
    }


    /**
     * 手動でQRコードを再生成したい場合（任意）
     */
    // public function regenerate()
    // {
    //     $today = now()->toDateString();

    //     $qr = DailyQr::updateOrCreate(
    //         ['date' => $today],
    //         ['token' => Str::random(32)]
    //     );

    //     return redirect()
    //         ->route('admin.qr.show')
    //         ->with('success', 'QRコードを再生成しました');
    // }
}

