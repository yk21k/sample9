<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\PickupOrder;
use App\Models\PickupOrderItem;
use Stripe\Stripe;
use Stripe\PaymentIntent;

class PickupCheckoutController extends Controller
{
    public function process(Request $request)
    {
        // 入力バリデーション
        $request->validate([
            'payment_method_id' => 'required|string',
            'items' => 'required|array',
            'pickup_location' => 'required|array',
        ]);

        // Stripe 初期化
        Stripe::setApiKey(config('services.stripe.secret'));

        // 合計金額（例：全商品の合計）
        $amount = 0;
        foreach ($request->items as $shopItems) {
            foreach ($shopItems as $item) {
                $amount += $item['price'];
            }
        }

        // Stripe PaymentIntent 作成 & 確定
        try {
            $intent = PaymentIntent::create([
                'amount' => $amount, // 円なら x100 に注意（例：1000円なら 100000 = 1000*100）
                'currency' => 'jpy',
                'payment_method' => $request->payment_method_id,
                'confirmation_method' => 'manual',
                'confirm' => true,
                'metadata' => [
                    'user_id' => Auth::id(),
                    'type' => 3, // デフォルトで「店舗受取」
                ],
            ]);
        } catch (\Exception $e) {
            return back()->withErrors(['payment' => '決済エラー: ' . $e->getMessage()]);
        }

        // DB保存: PickupOrder
        $order = PickupOrder::create([
            'user_id' => Auth::id(),
            'status' => 'confirmed',
            'payment_intent_id' => $intent->id,
            'type' => 3, // 店舗受取をデフォルト
        ]);

        // DB保存: PickupOrderItem（商品単位）
        foreach ($request->items as $shopItems) {
            foreach ($shopItems as $item) {
                PickupOrderItem::create([
                    'pickup_order_id' => $order->id,
                    'product_id' => $item['product_id'],
                    'shop_id' => $item['shop_id'],
                    'price' => $item['price'],
                    'pickup_date' => now()->toDateString(), // 仮で「今日」, 実際はフォームから
                    'pickup_time' => now()->addHours(2)->format('H:i:s'), // 仮で+2h
                    'pickup_slot_id' => $item['pickup_slot_id'],

                ]);
            }
        }

        return redirect()->route('checkout.success')->with('success', '決済が完了しました！');
    }

    public function success()
    {
        return view('checkout.success');
    }
}
