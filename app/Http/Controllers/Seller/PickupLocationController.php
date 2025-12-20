<?php

namespace App\Http\Controllers\Seller;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Models\PickupLocation;
use Illuminate\Support\Facades\Auth;

class PickupLocationController extends Controller
{
    // 登録フォーム
    public function create()
    {
        return view('sellers.pickuplocation.create');
    }

    // 登録処理
    public function store(Request $request)
    {
        $request->validate([
            'name'        => 'required|string|max:255',
            'address'     => 'required|string|max:255',
            'phone'       => 'nullable|string|max:20',
            'youtube_url' => [
                'nullable',
                'url',
                'max:255',
                function ($attribute, $value, $fail) {
                    // ✅ YouTube URL かどうかを判定
                    $pattern = '/^(https?:\/\/)?(www\.)?(youtube\.com\/watch\?v=|youtu\.be\/)[\w\-]{11}/';
                    if (!preg_match($pattern, $value)) {
                        // デバッグ用：ここが呼ばれているか確認
                        // dd("カスタムバリデーション発動: $value");
                        $fail('YouTubeの動画URLを入力してください。(YouTubeであってもプレイリストURLは動画URLではありません)');

                    }
                },
            ],
            'recorded_at' => 'required|date',
        ], [
            'name.required'        => '店舗名は必須です。',
            'address.required'     => '住所は必須です。',
            'youtube_url.url'      => 'YouTubeのURL形式で入力してください。',
            'recorded_at.required' => '撮影日時は必須です。',
            'recorded_at.date'     => '撮影日時は日付形式で入力してください。',
        ]);

        PickupLocation::create([
            'shop_id'      => Auth::user()->shop->id,
            'name'         => $request->name,
            'address'      => $request->address,
            'phone'        => $request->phone,
            'youtube_url'  => $request->youtube_url,
            'recorded_at'  => $request->recorded_at,
            'status'       => PickupLocation::STATUS_PENDING,
        ]);

        return redirect()->route('seller.pickuplocation.create')
                         ->with('success', '受取店舗を登録しました。管理者の承認待ちです。状況は、Shop Pick Up Indexのステータスからご確認ください');
    }

    // 一覧
    public function index()
    {
        $locations = PickupLocation::with('shop')->where('shop_id', auth()->user()->shop->id)->get();
        return view('sellers.pickuplocation.index', compact('locations'));
    }
}
