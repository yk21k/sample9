<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PickupSlot;
use App\Models\PickupReservation;
use Illuminate\Support\Facades\DB;

class PickupReservationController extends Controller
{
    // 決済完了後に呼ばれるStorePickupPaymentController　の188
    public function checkout(Request $request)
    {
        $cart = session('pickup_cart', []);
        if(empty($cart)) {
            return redirect()->route('pickup.cart.show')->withErrors('カートが空です');
        }

        DB::transaction(function() use ($cart) {
            foreach($cart as $item) {
                $slot = PickupSlot::find($item['slot_id']);

                // 残枠チェック
                if($slot->available() <= 0) {
                    throw new \Exception("時間帯が埋まりました: {$slot->date} {$slot->start_time}");
                }

                PickupReservation::create([
                    'pickup_slot_id' => $slot->id,
                    'user_id' => auth()->id(),
                    'status' => 'reserved',
                    'order_id' => null, // 決済システムと連携する場合にIDをセット
                ]);
            }
        });

        // カートクリア
        session()->forget('pickup_cart');

        return redirect()->route('pickup.reservations.index')->with('success', '予約が完了しました');
    }

    // 予約一覧
    public function index()
    {
        $reservations = auth()->user()->pickupReservations()->with('slot.product')->get();
        return view('pickup.reservations.index', compact('reservations'));
    }
}

