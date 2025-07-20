<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AuctionOrder;
use App\Models\User;
use Stripe\Stripe;
use Stripe\Transfer;
use Illuminate\Support\Facades\Log;


class StripePayController extends Controller
{
    public function handle($id)
    {
        $auctionOrder = AuctionOrder::findOrFail($id);

        $user = User::findOrFail($auctionOrder->shop->user_id);

        // Stripe APIキー
        Stripe::setApiKey(config('services.stripe.secret'));

        try {
            $transfer = Transfer::create([
                'amount' => $auctionOrder->final_price, 
                'currency' => 'jpy',
                'destination' => $user->stripe_account_id,
                'description' => "AuctionOrder #{$auctionOrder->id} payment to seller",
            ]);

            // 送金日時を記録（任意）
            $auctionOrder->transferred_at = now();
            $auctionOrder->save();

            return redirect()->back()->with([
                'message' => '送金が完了しました',
                'alert-type' => 'success'
            ]);
        } catch (\Exception $e) {
            return redirect()->back()->with([
                'message' => '送金に失敗しました: ' . $e->getMessage(),
                'alert-type' => 'error'
            ]);
        }
    }
}
