<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PickupOrder;
use App\Models\PickupOrderItem;
use Stripe\Stripe;
use Stripe\Checkout\Session as CheckoutSession;

class PickupOrderController extends Controller
{
    public function createCheckoutSession(Request $request)
    {
        // dd($request->all);
        $request->validate([
            'items' => 'required|array',
            'items.*.product_id' => 'required|integer',
            'items.*.pickup_date' => 'required|date|after_or_equal:today',
            'items.*.pickup_time' => 'required',
        ]);

        Stripe::setApiKey(config('services.stripe.secret'));

        $lineItems = [];
        foreach ($request->items as $item) {
            $lineItems[] = [
                'price_data' => [
                    'currency' => 'jpy',
                    'product_data' => [
                        'name' => $item['name'],
                        'metadata' => [
                            'type' => $item['type'], // 1=通常,2=オークション,3=店舗受取
                            'pickup_date' => $item['pickup_date'],
                            'pickup_time' => $item['pickup_time'],
                            'taxable' => $item['taxable'] ? 1 : 0,
                        ],
                    ],
                    'unit_amount' => $item['price'] * 100,
                ],
                'quantity' => $item['quantity'],
            ];
        }

        $session = CheckoutSession::create([
            'payment_method_types' => ['card'],
            'line_items' => $lineItems,
            'mode' => 'payment',
            'success_url' => route('pickup.checkout.success'),
            'cancel_url' => route('pickup.checkout.cancel'),
        ]);

        return response()->json(['id' => $session->id]);
    }

    public function handleWebhook(Request $request)
    {
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $endpointSecret = config('services.stripe.webhook_secret');

        try {
            $event = \Stripe\Webhook::constructEvent($payload, $sigHeader, $endpointSecret);
        } catch (\Exception $e) {
            return response('Webhook Error', 400);
        }

        if ($event->type === 'checkout.session.completed') {
            $session = $event->data->object;

            $order = PickupOrder::create([
                'user_id' => auth()->id(),
                'status' => 'confirmed',
                'payment_intent_id' => $session->payment_intent,
            ]);

            foreach ($session->display_items ?? [] as $line) {
                $metadata = $line->price->product->metadata;
                PickupOrderItem::create([
                    'pickup_order_id' => $order->id,
                    'product_id' => $metadata->product_id,
                    'shop_id' => $metadata->shop_id,
                    'price' => $line->amount_total / 100,
                    'pickup_date' => $metadata->pickup_date,
                    'pickup_time' => $metadata->pickup_time,
                    'type' => $metadata->type,
                    'taxable' => $metadata->taxable,
                ]);
            }
        }

        return response('Webhook received', 200);
    }
}
