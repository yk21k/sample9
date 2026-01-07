<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use App\Models\Shop;

class ShopLicenseController extends Controller
{
    public function show($shopId, $fileName)
    {
        $shop = Shop::findOrFail($shopId);

        // Shop にオーナー（owner()）が存在するか確認
        if (!$shop->owner) {
            abort(404, 'オーナーが見つかりません');
        }

        $userEmail = $shop->owner->email;

        // ファイルのパスを組み立て
        $dateFolder = now()->format('Ymd'); // 保存時と同じ形式
        $path = "licenses/{$dateFolder}/{$userEmail}/{$fileName}";

        // ファイル存在確認
        if (!Storage::exists($path)) {
            abort(404, 'ファイルが存在しません');
        }

        // ダウンロードレスポンス
        return Storage::download($path);
    }
}

