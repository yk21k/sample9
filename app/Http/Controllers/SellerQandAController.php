<?php

namespace App\Http\Controllers;

use App\Models\QandAPage;

class SellerQandAController extends Controller
{
    public function index()
    {
        // 公開中のQ&Aを表示順で取得
        $qandas = QandAPage::where('is_published', true)
            ->orderBy('sort_order')
            ->orderBy('created_at')
            ->get()
            ->groupBy('slug'); 
        // ※ slug をカテゴリ用途に使っている前提（後で category に分離してもOK）

        return view('qanda.index', compact('qandas'));
    }

    public function show(string $slug)
    {
        $qa = QandAPage::where('slug', $slug)
            ->where('is_published', true)
            ->firstOrFail();

        return view('qanda.show', compact('qa'));
    }
}

