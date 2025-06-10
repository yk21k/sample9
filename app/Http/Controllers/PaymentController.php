<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Stripe\Stripe;
use Stripe\Customer;
use Stripe\PaymentIntent;
use Stripe\Exception\ApiErrorException;
use App\Models\Order;  // Order モデルをインポート
use Illuminate\Support\Facades\Auth;



class PaymentController extends Controller
{
    // 支払い成功ページの表示
    public function success()
    {
        return view('stripe.success');  // success.blade.phpというビューを作成
    }

    public function createPaymentIntent(Request $request)
    {
        \Log::info("Stripe決済処理スタート");

        Stripe::setApiKey(config('services.stripe.secret'));

        $cartTotal = session('total_and_shipping', 0);
        $amount = intval($cartTotal);

        \Log::info("Stripe決済金額（受信）: " . $amount);

        $user = Auth::user();

        try {
            // 👤 顧客を作成（または取得）
            $customer = Customer::create([
                'email' => $user->email,
                'name'  => $user->name ?? 'No Name',
            ]);

            // 💳 PaymentIntent に顧客を関連付ける
            $paymentIntent = PaymentIntent::create([
                'amount' => $amount,           // 例：2980 → ¥2,980
                'currency' => 'jpy',
                'automatic_payment_methods' => ['enabled' => true],
            ]);
            \Log::info("Stripe PaymentIntent 作成成功: " . $paymentIntent->id);

            return response()->json([
                'clientSecret' => $paymentIntent->client_secret,
            ]);

        } catch (\Stripe\Exception\ApiErrorException $e) {
            \Log::error("Stripe APIエラー: " . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }




    
}

