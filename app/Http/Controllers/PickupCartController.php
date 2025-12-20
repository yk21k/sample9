<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;

use App\Models\PickupProduct;
use App\Models\PickupSlot;
use App\Models\Shop;
use App\Models\PickupLocation;
use Carbon\Carbon;
use Illuminate\Http\Request;

use Stripe\Stripe;
use Stripe\Checkout\Session as StripeSession;
use Illuminate\Support\Facades\Log;


class PickupCartController extends Controller
{
    // カートに追加
    public function add(Request $request, $id)
    {
        $pickupProduct = PickupProduct::findOrFail($id);
        if (!$pickupProduct->status) {
            return back()->withErrors('この商品は現在購入できません。');
        }

        // ✅ 在庫が 3 の場合は注意を表示
        if ($pickupProduct->stock <= 3) {
            $message = '⚠️ この商品は残り在庫がわずかです。決済した方が優先です。カートに入れても在庫は確保されません。';
        } else {
            $message = 'カートに追加しました。';
        }

        $cart = session()->get('pickup_cart', []);
        $cart[] = [
            'id' => uniqid(),
            'product_id' => $pickupProduct->id,
            'shop_id' => $pickupProduct->shop_id,
            'name' => $pickupProduct->name,
            'price' => $pickupProduct->price,
            // 初期値として null
            'pickup_date' => null,
            'pickup_time' => null,
            'pickup_location_id' => null,
            'quantity' => 1,
        ];
        session()->put('pickup_cart', $cart);

        return redirect('pickup/cart/index')->with('success', 'カートに追加しました');
    }

    /**
     * カート確認画面
     */
    public function index()
    {
        $cartItems = collect(session()->get('pickup_cart', []));
        $today = Carbon::today()->toDateString();
        $now = Carbon::now()->format('H:i:s');

        $cartItems = $cartItems->map(function ($item) use ($today, $now) {
            // $product = PickupProduct::with('shop', 'shop.pickupLocations')->find($item['product_id']);

            $product = \App\Models\PickupProduct::with(['shop_location', 'pick_location'])->find($item['product_id']);

            $item['product'] = $product;

            // 初期状態はスロットなし、日付選択時にAjaxで取得
            $item['availableSlots'] = collect();

            $item['shop_name'] = $product->shop->name ?? null;
            return (object)$item;
        });

        return view('pickup.cart.index', compact('cartItems'));
    }

    public function pickUpPlace()
    {
        $cart = collect(session()->get('pickup_cart', []));

        $cart = $cart->map(function ($item) {
            // 複数のリレーションをロード
            $product = \App\Models\PickupProduct::with(['shop_location', 'pick_location'])
                ->find($item['product_id']);

            // dd($product->shop_location, $product->pick_location);    

            if (!$product) return null;

            return [
                'product_name' => $product->name,
                'shop' => $product->shop_location,
                'pick' => $product->pick_location,
            ];

        })->filter();

        return view('pickup.place.place', compact('cart'));
    }

    /**
     * 個別スロット取得
     */
    public function getAvailableSlots(Request $request)
    {
        $productId = $request->product_id;
        $pickup_remaining_capacity = PickupSlot::where('pickup_product_id', $productId);

        $date = $request->date;
        $maxDate = Carbon::today()->addDays(14)->toDateString();

        if ($date < Carbon::today()->toDateString() || $date > $maxDate) {
            return response()->json(['slots' => []]);
        }

        $now = Carbon::now()->format('H:i:s');

        $slots = PickupSlot::where('pickup_product_id', $productId)
            ->whereDate('date', $date)
            ->where('remaining_capacity', '>', 0) // ✅ 追加：残枠があるスロットのみ
            ->when($date == Carbon::today()->toDateString(), function ($q) use ($now) {
                $q->whereTime('start_time', '>', date('H:i:s', strtotime($now . ' +1 hour')));
            })
            ->orderBy('start_time')
            ->get();

        return response()->json(['slots' => $slots]);
    }

    /**
     * 一括スロット取得（店舗共通）
     */
    public function getCommonSlots(Request $request)
    {
        $shopId = $request->shop_id;
        $date   = $request->date;
        $maxDate = Carbon::today()->addDays(14)->toDateString();

        if ($date < Carbon::today()->toDateString() || $date > $maxDate) {
            return response()->json(['commonSlots' => []]);
        }

        $cartItems = collect(session()->get('pickup_cart', []))
            ->where('shop_id', $shopId)
            ->map(function ($item) use ($date) {
                $slots = PickupSlot::where('pickup_product_id', $item['product_id'])
                    ->where('remaining_capacity', '>', 0) // ✅ 追加：残枠があるスロットのみ
                    ->whereDate('date', $date)
                    ->orderBy('start_time')
                    ->get();
                $item['availableSlots'] = $slots;
                return (object)$item;
            });

        $commonSlots = $cartItems->pluck('availableSlots')->reduce(function ($carry, $slots) {
            if (is_null($carry)) return $slots;
            return $carry->filter(fn($slot) => $slots->pluck('id')->contains($slot->id));
        }, null);

        return response()->json(['commonSlots' => $commonSlots ?? []]);
    }

    /**
     * 一括スロット更新
     */
    public function updateAllSlots(Request $request)
    {
        $shopId = $request->shop_id;
        $slotId = $request->pickup_slot_id;

        $cart = session()->get('pickup_cart', []);
        foreach($cart as &$item){
            if($item['shop_id'] == $shopId){
                $item['pickup_slot_id'] = $slotId;
            }
        }
        session()->put('pickup_cart', $cart);

        return redirect()->back()->with('success', 'スロットを更新しました');
    }

    public function updatePickupInfo(Request $request)
    {
        \Log::info('updatePickupInfo called', $request->all());

        // 現在のカートを取得
        $cart = collect(session('pickup_cart', []));

        $cartId = $request->cart_id ?? null;
        $pickupDate = $request->pickup_date ?? [];
        $pickupTime = $request->pickup_time ?? [];
        $pickupLocation = $request->pickup_location ?? [];


        // JS から送られる pickup_info は配列
        $pickupInfoList = $request->pickup_info ?? [];

        \Log::info('updatePickupInfo pickup_info', $request->pickup_info);

        $cart = $cart->map(function ($item) use ($pickupInfoList) {
            foreach ($pickupInfoList as $cartId => $info) {
                // $cartId が cart_id として使える
                $isMatch = ($item['id'] === $cartId);

                \Log::info('比較チェック', [
                    'item_id' => $item['id'],
                    'cart_id' => $cartId,
                    'result' => $isMatch ? '✅ 一致' : '❌ 不一致'
                ]);

                if ($isMatch) {
                    $item['pickup_date'] = $info['pickup_date'] ?? $item['pickup_date'];
                    $item['pickup_time'] = $info['pickup_time'] ?? $item['pickup_time'];
                    $item['pickup_location_id'] = $info['pickup_location_id'] ?? $item['pickup_location_id'];
                    $item['pickup_slot_id'] = $info['pickup_slot_id'] ?? $item['pickup_slot_id'];
                    \Log::info('✅ Updated item ✅ pickup_slot_id ', $item);
                }
            }
            return $item;
        });

        // ✅ Collectionを配列に変換してセッション保存
        session(['pickup_cart' => $cart->toArray()]);
        session()->put('pickup_cart', $cart);
        session()->save();

        \Log::info('After Update', ['cart' => $cart]);

        return response()->json(['success' => true, 'cart' => $cart]);
    }


    // 個別削除
    public function remove($id)
    {
        $cartItems = collect(session()->get('pickup_cart', []));
        $cartItems = $cartItems->reject(fn($item) => $item['id'] == $id)->values();
        session(['pickup_cart' => $cartItems]);
        return redirect()->back();
    }

    // カート全削除
    public function clear()
    {
        session()->forget('pickup_cart');
        return redirect()->back();
    }

    // チェックアウト前のバリデーション
    public function proceedToCheckout(Request $request)
    {
        $cart = collect(session()->get('pickup_cart', []));
        if ($cart->isEmpty()) {
            return redirect()->route('pickup.cart.index')
                ->withErrors('カートが空です。');
        }

        $today = Carbon::today();
        $now   = Carbon::now();
        $limit = $now->copy()->addHours(2)->format('H:i:s'); // 今日なら2時間前まで予約可能

        foreach ($cart as $item) {
            // --- 必須チェック ---
            if (empty($item['date']) || empty($item['slot_id'])) {
                return redirect()->route('pickup.cart.index')
                    ->withErrors('全ての商品に受取日と時間帯を選択してください。');
            }

            // --- 日付チェック ---
            $date = Carbon::parse($item['date']);
            if ($date->lt($today)) {
                return redirect()->route('pickup.cart.index')
                    ->withErrors('過去の日付は選択できません。');
            }
            if ($date->gt($today->copy()->addDays(14))) {
                return redirect()->route('pickup.cart.index')
                    ->withErrors('受取日は本日から14日以内で選択してください。');
            }

            // --- スロット存在チェック ---
            $slot = PickupSlot::find($item['slot_id']);
            if (!$slot || $slot->pickup_product_id != $item['product_id']) {
                return redirect()->route('pickup.cart.index')
                    ->withErrors('選択されたスロットが不正です。');
            }

            // --- 今日の場合の時間チェック ---
            if ($date->isToday()) {
                if ($slot->start_time < $limit) {
                    return redirect()->route('pickup.cart.index')
                        ->withErrors('本日のスロットは現在時刻から▲時間後以降のみ選択可能です。');
                }
            }

            // --- 日付とスロットの整合性チェック ---
            if ($slot->date != $date->toDateString()) {
                return redirect()->route('pickup.cart.index')
                    ->withErrors('選択されたスロットの日付が一致しません。');
            }
        }
        // --- セッションに最新情報を書き戻す ---
        session()->put('pickup_cart', $cart->toArray());
        session()->put('pickup_cart', $cart);

        session()->save(); // 確定書き込み！

        // --- ここまで通過したら checkout へ ---
        return redirect()->route('pickup.cart.checkout', compact('cart'));
    }

    // カート確認 → Stripe決済画面
    public function checkout()
    {
        $cart = collect(session()->get('pickup_cart', []));

        if ($cart->isEmpty()) {
            return redirect()->route('pickup.cart.index')->with('info', 'カートに商品がありません');
        }
        // dd($cart);

        // --- セッションに最新情報を書き戻す ---
        session()->put('pickup_cart', $cart->toArray());
        session()->put('pickup_cart', $cart);

        session()->save(); // 確定書き込み！

        // dd(session()->all());

        // 店舗・受取場所情報・課税判定・数量を付与
        $cart = $cart->map(function ($item) {
            // 商品・ショップ・受取場所をロード

            $product = \App\Models\PickupProduct::with(['shop_location', 'pick_location', 'slots'])
                ->find($item['product_id']);


            if (!$product) return null;

            $item['product'] = $product;
            $item['quantity'] = $item['quantity'] ?? 1;

            // 課税判定（invoice_number があれば課税）
            $item['is_taxable'] = $product->shop->invoice_number ? true : false;

            // dd($item['pickup_slot']);
            // dd($item['date']);
            // dd($product);

            // 受取スロット情報がある場合は使用
            $item['pickup_slot'] = PickupSlot::find($item['slot_id'] ?? null);

            $item['pickup_date'] = $item['pickup_date'] ?? ($item['date'] ?? null);
            $slot = PickupSlot::find($item['pickup_time'] ?? null);//$item['pickup_time']はpickup_slot_id
            if ($slot) {
                $item['pickup_time'] = $slot->start_time; // 'HH:MM:SS'
            }

            // 各商品に紐づく受取場所情報
            $item['pickup_locations'] = $product->shop->pickupLocations ?? collect();

            // pickup_location_id がまだなければ最初の location をセット
            $item['pickup_location_id'] = $item['pickup_location_id'] ?? ($item['pickup_locations']->first()->id ?? null);


            // dd($product);
            // $item['pickup_slot_id'] = $product->slots->first()?->id;
            // $item['pickup_slot_id'] = $item['pickup_slot_id'];


            return $item;
        })->filter(); // null 削除

        // --- セッションに最新情報を書き戻す ---
        session()->put('pickup_cart', $cart->toArray());
        session()->save(); // 確定書き込み！

        // dd($cart);

        // 課税率を取得（例: 10%）
        $taxRate = \App\Models\TaxRate::current()?->rate ?? 0;

        // 合計金額計算（税込・数量対応）
        $total = $cart->reduce(function ($carry, $item) use ($taxRate) {
            $price = $item['product']->price ?? 0;
            $quantity = $item['quantity'] ?? 1;
            $isTaxable = $item['is_taxable'] ?? false;

            // 課税なら税込価格を計算
            $subtotal = $isTaxable ? $price * (1 + $taxRate) : $price;
            return $carry + ($subtotal * $quantity);
        }, 0);

        return view('pickup.cart.checkout', compact('cart', 'total'));
    }




}


