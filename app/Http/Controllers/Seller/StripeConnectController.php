<?php

namespace App\Http\Controllers\Seller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\User; // 必要なら上に追加

use App\Http\Controllers\Controller;


class StripeConnectController extends Controller
{
    public function redirectToStripe()
    {
        $clientId = config('services.stripe.client_id');
        $redirectUri = config('services.stripe.redirect_uri');

        $state = encrypt(Auth::id()); // 安全な暗号化された user_id

        $url = 'https://connect.stripe.com/oauth/authorize?' . http_build_query([
            'response_type' => 'code',
            'client_id' => $clientId,
            'scope' => 'read_write',
            'redirect_uri' => $redirectUri,
            'state' => $state,
            'always_prompt' => 'true',
        ]);

        return redirect($url);
    }


    public function handleCallback(Request $request)
    {
        if ($request->has('error')) {
            return redirect()->route('dashboard')->with([
                'message' => 'Stripe接続がキャンセルされました。',
                'alert-type' => 'error',
            ]);
        }

        $response = Http::asForm()->post('https://connect.stripe.com/oauth/token', [
            'grant_type' => 'authorization_code',
            'client_id' => config('services.stripe.client_id'),
            'client_secret' => config('services.stripe.secret'),
            'code' => $request->code,
        ]);

        if ($response->failed()) {
            Log::error('Stripe OAuth 失敗', ['response' => $response->body()]);
            return redirect()->route('dashboard')->with([
                'message' => 'Stripe接続に失敗しました。',
                'alert-type' => 'error',
            ]);
        }

        $stripeAccountId = $response->json('stripe_user_id');

        // state から復元
        if (!$request->has('state')) {
            abort(400, 'Invalid state');
        }
        $userId = decrypt($request->state);
        $user = User::find($userId);

        if ($user) {
            $user->stripe_account_id = $stripeAccountId;

            // ✅ 任意のアカウント名を保存（例：ユーザーの氏名またはショップ名）
            $user->stripe_account_name = $user->name ?? '未設定';

            $user->save();

            Log::info('Stripe account ID and name saved', [
                'user_id' => $user->id,
                'account_id' => $stripeAccountId,
                'account_name' => $user->stripe_account_name,
            ]);

            return redirect()->route('dashboard')->with([
                'message' => 'Stripe接続が完了しました！',
                'alert-type' => 'success',
            ]);
        } else {
            Log::warning('User not found from decrypted state');
            return redirect()->route('dashboard')->with([
                'message' => 'ユーザーが見つかりません。',
                'alert-type' => 'error',
            ]);
        }
    }



}    
