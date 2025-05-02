<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Stripe\Stripe;
use Stripe\Customer;
use Stripe\PaymentIntent;
use Stripe\Exception\ApiErrorException;
use App\Models\Order;  // Order モデルをインポート

class PaymentController extends Controller
{
    // 支払い成功ページの表示
    public function success()
    {
        return view('stripe.success');  // success.blade.phpというビューを作成
    }

    public function createPaymentIntent(Request $request)
    {
        \Stripe\Stripe::setApiKey(config('services.stripe.secret'));

        // ★ 例：セッションからカート合計金額を取得（あなたのロジックに合わせて調整）
        $cartTotal = session('cart_total', 0); // 例：¥2980 が入っている想定

        // Stripeは最小単位（円単位ならそのままでOK）
        $amount = intval($cartTotal); // 念のため整数にしておく
        
        // dd($cartTotal, $amount);

        try {
            $paymentIntent = \Stripe\PaymentIntent::create([
                'amount' => $amount,           // 例：2980 → ¥2,980
                'currency' => 'jpy',
                'automatic_payment_methods' => ['enabled' => true],
            ]);

            return response()->json([
                'clientSecret' => $paymentIntent->client_secret,
            ]);

        } catch (\Stripe\Exception\ApiErrorException $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }


    
}

