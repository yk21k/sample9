<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Commition;
use App\Models\Shop;
use Stripe\Stripe;
use Stripe\Transfer;
use Illuminate\Support\Facades\Log;

use App\Models\SubOrder;  
class StripeTransferController extends Controller
{
    public function transfer($id)
    {
        Log::info('Stripe送金開始', ['sub_order_id' => $id]);
        
        $subOrder = SubOrder::findOrFail($id);

        $user = User::findOrFail($subOrder->seller_id);
        $shopId = Shop::where('user_id', $subOrder->seller_id)->first();
        $rate_fee = Commition::find($shopId)->first();

        $fixed = $rate_fee->fixed ?? 0;
        $rate = $rate_fee->rate ?? 0;
        $grandTotal = $subOrder->grand_total ?? 0;

        // 手数料の合計を切り捨てて整数化
        $fee = (int) floor($fixed + ($grandTotal * $rate));

        Log::info('ユーザー情報', ['user' => $user]);
        Log::info('セラー情報', ['seller' => $subOrder->seller_id]);
        Log::info('shopId', ['shopId' => $shopId]);
        Log::info('rate_fee', ['rate_fee' => $rate_fee]);
        Log::info('fee', ['fee' => $rate_fee->fixed+($subOrder->grand_total*$rate_fee->rate)]);
        Log::info('real_fee', ['real_fee' => $fee]);

        Stripe::setApiKey(config('services.stripe.secret'));

        try {
            $transfer = Transfer::create([
                'amount' => $subOrder->grand_total - $fee,
                'currency' => 'jpy',
                'destination' => $user->stripe_account_id,
            ]);

            // ✅ 送金日時を記録
            $subOrder->transferred_at = now();
            $subOrder->save();

            Log::info('送金成功', ['transfer_id' => $transfer->id]);

            return redirect()->back()->with([
                'message' => '送金成功！ID: ' . $transfer->id,
                'alert-type' => 'success',
            ]);
        } catch (\Exception $e) {
            Log::error('送金エラー', ['message' => $e->getMessage()]);
            return redirect()->back()->with([
                'message' => '送金失敗: ' . $e->getMessage(),
                'alert-type' => 'error',
            ]);
        }

    }
}    

