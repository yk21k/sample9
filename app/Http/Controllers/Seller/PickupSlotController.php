<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PickupSlot;
use App\Models\PickupProduct;
use Carbon\Carbon;
use App\Models\Calendar\HolidaySetting;
use App\Models\Calendar\ExtraHoliday;


class PickupSlotController extends Controller
{
    // スロット一覧（管理者用）
    public function index(PickupProduct $product)
    {
        $shopId = auth()->user()->shop->id;

        $slots = PickupSlot::with('product')->whereHas('product', function ($query) use ($shopId) {
                $query->where('shop_id', $shopId);
            })
            ->orderBy('date')
            ->orderBy('start_time')
            ->get();

        return view('sellers.pickup.index', compact('slots', 'product'));
    }

    // スロット作成フォーム
    public function create()
    {
        $products = PickupProduct::where('shop_id', auth()->user()->shop->id)->get(); // 商品と紐づけるため
        return view('sellers.pickup.create', compact('products'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'pickup_product_id' => 'required|exists:pickup_products,id',
            'date' => 'required|date',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'capacity' => 'required|integer|min:1',
        ]);

        // ✅ capacity と同じ値を remaining_capacity に設定
        $validated['remaining_capacity'] = $validated['capacity'];

        PickupSlot::create($validated);

        return back()->with('success', 'スロットを作成しました');
    }

    // 月単位生成
    public function generateMonthlySlots(Request $request, PickupProduct $product)
    {
        $month = $request->input('month'); // YYYY-MM
        $times = $request->input('times'); // [['start'=>'10:00','end'=>'12:00','capacity'=>5], ...]

        $startDate = Carbon::parse($month . '-01');
        $endDate = $startDate->copy()->endOfMonth();

        // 定休日を取得
        $shopId = $product->shop_id;

        // 1. 定休日の取得
        $holidaySetting = HolidaySetting::where('shop_id', $shopId)->first();

        // 曜日フラグを配列に変換
        $weeklyHolidays = [];
        if ($holidaySetting) {
            if (in_array($holidaySetting->flag_mon, [2])) $weeklyHolidays[] = 1;
            if (in_array($holidaySetting->flag_tue, [2])) $weeklyHolidays[] = 2;
            if (in_array($holidaySetting->flag_wed, [2])) $weeklyHolidays[] = 3;
            if (in_array($holidaySetting->flag_thu, [2])) $weeklyHolidays[] = 4;
            if (in_array($holidaySetting->flag_fri, [2])) $weeklyHolidays[] = 5;
            if (in_array($holidaySetting->flag_sat, [2])) $weeklyHolidays[] = 6;
            if (in_array($holidaySetting->flag_sun, [2])) $weeklyHolidays[] = 0;
        }

        // 2. 臨時休業日（個別日付）
        $extraHolidays = ExtraHoliday::where('shop_id', $shopId)
                        ->where('date_flag', 2) // 1 = 休業
                        ->pluck('date_key')
                        ->toArray();
                         
        $today = Carbon::today();                                        

        // 3. スロット生成
        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {

            // (A) 過去日スキップ
            if ($date->lt($today)) {
                continue;
            }

            // (B) 定休日スキップ
            if (in_array($date->dayOfWeek, $weeklyHolidays)) {
                continue;
            }

            // (C) 臨時休業スキップ
            if (in_array($date->format('Ymd'), $extraHolidays)) {
                continue;
            }

            foreach ($times as $slotTime) {
                PickupSlot::create([
                    'pickup_product_id' => $product->id,
                    'date' => $date->toDateString(),
                    'start_time' => $slotTime['start'],
                    'end_time' => $slotTime['end'],
                    'capacity' => $slotTime['capacity'],
                    'remaining_capacity' => $slotTime['capacity'],
                    'is_active' => true,
                ]);
            }
        }

        return redirect()->back()->with('success', 'スロットを自動生成しました');
    }

    // 前月コピー
    public function copyPreviousMonth(PickupProduct $product)
    {
        $thisMonth = Carbon::now()->startOfMonth();
        $previousMonth = $thisMonth->copy()->subMonth();

        $prevSlots = PickupSlot::where('pickup_product_id', $product->id)
                        ->whereMonth('date', $previousMonth->month)
                        ->whereYear('date', $previousMonth->year)
                        ->get();

        foreach ($prevSlots as $slot) {
            $day = Carbon::parse($slot->date)->day;
            $newDate = $thisMonth->copy()->day($day);

            if (!$newDate->isValid()) continue;

            PickupSlot::create([
                'pickup_product_id' => $slot->pickup_product_id,
                'date' => $newDate->toDateString(),
                'start_time' => $slot->start_time,
                'end_time' => $slot->end_time,
                'capacity' => $slot->capacity,
                'remaining_capacity' => $slot->capacity,
                'is_active' => true
            ]);
        }

        return redirect()->back()->with('success', '前月のスロットをコピーしました');
    }

    public function edit(PickupSlot $pickupSlot)
    {
        return view('sellers.pickup.edit', compact('pickupSlot'));
    }

    public function update(Request $request, PickupSlot $pickupSlot)
    {
        $pickupSlot->update($request->validate([
            'date' => 'required|date',
            'start_time' => 'required',
            'end_time' => 'required|after:start_time',
            'capacity' => 'required|integer|min:1',
            'remaining_capacity' => 'required|integer|min:1',
        ]));

        return redirect()->back()->with('success', 'スロットを更新しました');
    }

    public function destroy(PickupSlot $pickupSlot)
    {
        $pickupSlot->delete();

        return redirect()->back()->with('success', 'スロットを削除しました');
    }
}