<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Stripe\Stripe;
use Stripe\Customer;
use Stripe\PaymentIntent;
use Stripe\Exception\ApiErrorException;
use Illuminate\Support\Facades\Auth;
use App\Models\PickupOrder;
use App\Models\PickupOrderItem;
use App\Models\PickupProduct;
use App\Models\PickupReservation;
use App\Models\PickupSlot;
use App\Models\TaxRate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class StorePickupPaymentController extends Controller
{
    /**
     * PaymentIntent 作成
     */
    public function createPaymentIntent(Request $request)
    {
        \Log::info("Pick Up Stripe決済処理スタート");

        Stripe::setApiKey(config('services.stripe.secret'));

        $cart = collect(session()->get('pickup_cart', [])); // session から取得

        if ($cart->isEmpty()) {
            return response()->json(['error' => 'カートが空です'], 400);
        }

        // 安全に metadata 用の値を作成
        $pickupLocation = $cart->first()['pickup_location_id'] ?? '未指定';
        $pickupDate     = $cart->first()['pickup_date'] ?? '未指定';
        $pickupTime     = $cart->first()['pickup_time'] ?? '未指定';

        $taxRate = TaxRate::current()?->rate ?? 0.1; // 例：10%


        // 合計金額計算（税込・数量対応）
        $totalAmount = $cart->reduce(function ($carry, $item) use ($taxRate){
            $price = $item['price'] ?? 0;
            $quantity = $item['quantity'] ?? 1;
            $isTaxable = $item['is_taxable'] ?? false;

            // もし $isTaxable が真（true）なら税を加える。そうでなければそのまま。

            $subtotal = $isTaxable ? $price * (1 + $taxRate) : $price;

            // ログ出力（課税・非課税の確認）
            \Log::info("Pick Up Stripe: " . ($isTaxable ? '課税' : '非課税') . " | price={$price}, qty={$quantity}, subtotal={$subtotal}");

            return $carry + ($subtotal * $quantity);
        }, 0);


        // 最低1円以上に丸める
        $amount = max(intval($totalAmount), 1);

        $user = $request->user(); // ログインユーザー

        try {
            $customer = \Stripe\Customer::create([
                'email' => $user->email,
                'name'  => $user->name ?? 'No Name',
            ]);

            $paymentIntent = \Stripe\PaymentIntent::create([
                'amount' => $amount,           // 円単位で整数
                'currency' => 'jpy',
                'customer' => $customer->id,
                'automatic_payment_methods' => ['enabled' => true],
                'metadata' => [
                    'user_id' => $user->id,
                    'cart_items' => json_encode($cart->pluck('product_id')->toArray()),
                    'receipt_type' => 'pick up', // 👈 ここで追加！
                    'pickup_location' => $pickupLocation,
                    'pickup_date'     => $pickupDate,
                    'pickup_time'     => $pickupTime,
                ],
            ]);

            return response()->json([
                'clientSecret' => $paymentIntent->client_secret,
                'amount' => $amount
            ]);

        } catch (\Stripe\Exception\ApiErrorException $e) {
            \Log::error("Pick Up Stripe APIエラー: " . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function storeOrder(Request $request)
    {
        $cart = collect(session()->get('pickup_cart', []));

        if ($cart->isEmpty()) {
            \Log::warning('⚠️ pickup_cart is empty');
            return response()->json(['error' => 'カートが空です'], 400);
        }

        $taxRate = TaxRate::current()?->rate ?? 0.1;
        $user = Auth::user();

        \Log::info('🧾🧾🧾 pickup_cart session:', $cart->toArray());

        try {
            $result = DB::transaction(function () use ($cart, $request, $user, $taxRate) {
                // 親注文作成
                $pickupOrder = PickupOrder::create([
                    'user_id'           => $user->id,
                    'payment_intent_id' => $request->payment_intent_id,
                    'status'            => '1', // 決済完了
                ]);

                $errors = [];

                foreach ($cart as $item) {
                    $item = (array) $item;

                    $pickupDate = $item['pickup_date'] ?? now()->format('Y-m-d');
                    $pickupTime = $item['pickup_time'] ?? '12:00:00';
                    $pickupSlotId = $item['pickup_slot_id'] ?? null;

                    // 受取スロット
                    $slot = null;
                    $reservation = null;

                    if ($pickupSlotId) {
                        $slot = PickupSlot::find($pickupSlotId);
                        if (!$slot) {
                            $errors[] = "受取スロットが見つかりません (ID: {$pickupSlotId})";
                            continue;
                        }
                        if ($slot->remaining_capacity < $item['quantity']) {
                            $errors[] = "受取枠が不足しています: {$item['product_name']}";
                            continue;
                        }

                        // 枠を減算
                        $slot->decrementCapacity($item['quantity']);

                        // ✅ Reservationを先に作る（一時的に null）
                        $reservation = PickupReservation::create([
                            'pickup_slot_id'       => $slot->id,
                            'order_id'             => $pickupOrder->id,
                            'user_id'              => $user->id,
                            'quantity'             => $item['quantity'],
                            'pickup_order_item_id' => null,
                        ]);
                    }

                    // 税・商品登録
                    $product = PickupProduct::lockForUpdate()->find($item['product_id']);
                    if (!$product) {
                        $errors[] = "商品が見つかりません (ID: {$item['product_id']})";
                        continue;
                    }

                    $isTaxable = !empty($product->shop->invoice_number ?? null);
                    $price = $isTaxable
                        ? round($item['price'] * (1 + $taxRate))
                        : $item['price'];

                    // ✅ OrderItem作成
                    $pickupOrderItem = PickupOrderItem::create([
                        'pickup_order_id'     => $pickupOrder->id,
                        'product_id'          => $product->id,
                        'shop_id'             => $product->shop_id,
                        'price'               => $price,
                        'quantity'            => $item['quantity'],
                        'pickup_date'         => $pickupDate,
                        'pickup_time'         => $pickupTime,
                        'pickup_slot_id'      => $slot?->id,
                        'pickup_location_id'  => $item['pickup_location_id'] ?? null,
                        'type'                => 3,
                    ]);

                    // ✅ Reservationがある場合はpickup_order_item_idを上書き
                    if ($reservation) {
                        $reservation->update([
                            'pickup_order_item_id' => $pickupOrderItem->id,
                        ]);

                        \Log::info('🔗 Reservation更新完了', [
                            'reservation_id' => $reservation->id,
                            'linked_item_id' => $pickupOrderItem->id,
                        ]);
                    } else {
                        \Log::info('⚠️ Reservation未作成のためリンクスキップ', [
                            'item_id' => $pickupOrderItem->id,
                        ]);
                    }
                }


                // エラーがあった場合は注文キャンセル扱い
                if (!empty($errors)) {
                    throw new \Exception(implode("\n", $errors));
                    \Log::info('✅remaining_capacity ', [
                        'product_id' => $product->id,
                        'quantity' => $quantity,
                        'stock_remaining' => $product->stock,
                        'slot_remaining' => $slot?->remaining_capacity,
                    ]);
                }

                return $pickupOrder;
            });

            // セッションカートクリア
            session()->forget('pickup_cart');

            return response()->json([
                'success' => true,
                'order_id' => $result->id,
                'message' => '注文が正常に作成されました。',
            ]);

        } catch (\Throwable $e) {
            \Log::error('❌ PickupOrder 作成エラー: ' . $e->getMessage());
            report($e);

            // エラーメッセージをユーザーに返す
            return response()->json([
                'error' => $e->getMessage() ?: '注文作成に失敗しました。',
            ], 409);
        }
    }

    public function checkStock(Request $request)
    {
        $cart = collect($request->input('cart', []));
        $errors = [];

        foreach ($cart as $item) {
            $item = (array) $item;
            $product = PickupProduct::find($item['product_id']);

            if (!$product) {
                $errors[] = "商品が見つかりません (ID: {$item['product_id']})";
                continue;
            }

            $quantity = $item['quantity'] ?? 1;
            if ($product->stock < $quantity) {
                $errors[] = "在庫が不足しています: {$product->name}";
            }

            if (!empty($item['pickup_slot_id'])) {
                $slot = PickupSlot::find($item['pickup_slot_id']);
                if (!$slot) {
                    $errors[] = "受取スロットが見つかりません (ID: {$item['pickup_slot_id']})";
                    continue;
                }
                if ($slot->remaining_capacity < $quantity) {
                    $errors[] = "受取枠が不足しています: {$product->name}";
                    \Log::info('✅remaining_capacity ', [
                        'product_id' => $product->id,
                        'quantity' => $quantity,
                        'stock_remaining' => $product->stock,
                        'slot_remaining' => $slot?->remaining_capacity,
                    ]);
                }
            }
        }

        if (!empty($errors)) {
            return response()->json([
                'success' => false,
                'message' => implode("\n", $errors),
            ], 400);
        }

        return response()->json(['success' => true]);
    }

    /**
     * 支払い成功ページ（オプション）
     */
    public function success()
    {
        return view('stripe.success'); 
    }
}
