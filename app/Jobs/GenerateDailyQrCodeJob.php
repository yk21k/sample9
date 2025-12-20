<?php

namespace App\Jobs;

use App\Models\DailyQrCode;
use Illuminate\Support\Facades\URL;

class GenerateDailyQrCodeJob extends Job
{
    public function handle()
    {
        // 24時間だけ有効な署名URL
        $signedUrl = URL::temporarySignedRoute(
            'shop_staff.login.signed',
            now()->addDay(),
            ['token' => uniqid()]
        );

        // 保存（上書きしたいので既存を削除）
        DailyQrCode::truncate();

        DailyQrCode::create([
            'signed_url' => $signedUrl,
            'expires_at' => now()->addDay(),
        ]);
    }
}

