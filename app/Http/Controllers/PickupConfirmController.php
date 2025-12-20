<?php

namespace App\Http\Controllers;

use App\Models\PickupOrder;
use App\Models\PickupOrderItem;
use Illuminate\Http\Request;

class PickupConfirmController extends Controller
{
    public function receiveItem($id)
    {
        $item = PickupOrderItem::findOrFail($id);

        // ログインユーザー本人の注文に紐づく商品かチェック
        if ($item->order->user_id !== auth()->id()) {
            abort(403, '不正なアクセスです');
        }

        // すでに受取済みの場合はスキップ
        if ($item->status === 'received') {
            return back()->with('info', 'この商品はすでに受取済みです。');
        }

        // 受取処理
        $item->update([
            'status' => 'received',
            'received_at' => now(),
        ]);



        return back()->with('success', '商品を受取済みに更新しました。');
    }

}

