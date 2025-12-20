<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Stripe\Stripe;
use Stripe\AccountLink;
use Stripe\Account;
use Auth;

class StripeOnboardingController extends Controller
{
public function redirectToStripe(Request $request)
{
    \Stripe\Stripe::setApiKey(config('services.stripe.secret'));

    $user = Auth::user(); // ① ユーザー取得

    // ② Stripeアカウントが未作成なら作成して保存
    if (empty($user->stripe_account_id)) {
        $account = \Stripe\Account::create([
            'type' => 'express',
            'country' => 'JP',
            'email' => $user->email,
            'capabilities' => [
                'transfers' => ['requested' => true],
            ],
        ]);

        $user->stripe_account_id = $account->id;
        $user->save();
    }

    // ③ アカウント状態を確認（デバッグ用）
    try {
        $account = \Stripe\Account::retrieve($user->stripe_account_id);

        // 状態を確認したいだけならここで止める（開発中のみ）
        
    } catch (\Exception $e) {
        return redirect()->back()->withErrors('Stripe アカウントの取得に失敗しました: ' . $e->getMessage());
    }

    // ④ オンボーディングリンクを作成してリダイレクト
    $accountLink = \Stripe\AccountLink::create([
        'account' => $user->stripe_account_id,
        'refresh_url' => route('stripe.onboarding'),
        'return_url' => url('/admin'), // 任意の戻り先
        'type' => 'account_onboarding',
    ]);

    return redirect($accountLink->url);
}

}

