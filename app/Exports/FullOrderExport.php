<?php

namespace App\Exports;

use App\Models\SubOrder;
use App\Models\Shop;
use App\Models\Campaign;
use App\Models\ShopCoupon;
use App\Models\Commition;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Carbon\Carbon;

class FullOrderExport implements FromCollection
{
    public function collection()
    {
        $today = Carbon::today();
        $rows = collect();
        $rows->push(['出力日: ' . now()->format('Y-m-d')]); // ✅ 1行目に追加

        // ✅ ヘッダー行（1行目）
        $rows->push([
            // 注文情報
            'オーダーNo.', 'ID', '状況', '注文日', '宛名', '電話番号', '郵便番号', '宛先',

            // 商品情報
            '商品名', '個数', '通常価格(配送料なし)', '配送料', '手数料対象額', '支払手数料', '入金額(予定も含む)',

            // 価格関連
            '通常価格(配送料込)', 'キャンペーン価格', 'クーポン価格', '最終価格', '適用(２つ目以降は適用なし)'
        ]);

        // ✅ 対象の SubOrder 取得（管理者 or セラー）
        $user = auth()->user();
        \Log::info('現在のログインユーザー', ['id' => $user->id, 'name' => $user->name]);
        $subOrders = $user->id == 1
            ? SubOrder::with(['order', 'items'])->get()
            : SubOrder::with(['order', 'items'])->where('seller_id', $user->id)->get();

        foreach ($subOrders as $subOrder) {
            $order = $subOrder->order;
            $items = $subOrder->items;

            foreach ($items as $item) {
                // === 基本価格情報 ===
                $quantity = $item->pivot->quantity ?? 1;
                $basePrice = (float) $item->price;
                $shippingFee = (float) $item->shipping_fee;
                $originalPriceWithShipping = $basePrice + $shippingFee;
                $rate_and_fixed = Commition::first();
                $feeRate = $rate_and_fixed->rate;
                $feeFixed = $rate_and_fixed->fixed;

                $fee = (int) ($basePrice * $quantity * $feeRate + $feeFixed);

                // === 店舗・クーポン・キャンペーン取得 ===
                $shop = Shop::where('user_id', $subOrder->seller_id)->first();
                $coupon = ShopCoupon::where('code', $subOrder->coupon_code)->first();
                $campaign = Campaign::where('shop_id', $shop->id ?? null)
                    ->where('status', 1)
                    ->where('start_date', '<=', $today)
                    ->where('end_date', '>=', $today)
                    ->orderByDesc('dicount_rate1')
                    ->first();

                // === 割引計算 ===
                $campaignPrice = $campaign
                    ? ceil($basePrice - $basePrice * $campaign->dicount_rate1)
                    : $basePrice;
                $campaignPriceWithShipping = $campaignPrice + $shippingFee;

                $couponPrice = $coupon
                    ? max($basePrice + $coupon->value, 0)
                    : $basePrice;
                $couponPriceWithShipping = $couponPrice + $shippingFee;

                $lowestUnitPrice = min($originalPriceWithShipping, $campaignPriceWithShipping, $couponPriceWithShipping);

                $expectedTotal = ceil($lowestUnitPrice + $basePrice * max($quantity - 1, 0) + $shippingFee * $quantity - $fee);

                // === 適用ラベル ===
                $appliedLabel = '適用なし';
                if ($campaign && $campaignPriceWithShipping <= $couponPriceWithShipping) {
                    $appliedLabel = 'キャンペーン適用';
                } elseif ($coupon) {
                    $appliedLabel = 'クーポン適用';
                }

                // === Excel 1行分 push ===
                $rows->push([
                    // 注文情報
                    $order->order_number ?? '',
                    $subOrder->id,
                    $subOrder->status,
                    optional($subOrder->created_at)->format('Y-m-d H:i:s'),
                    $order->shipping_fullname ?? '',
                    $order->shipping_phone ?? '',
                    $order->shipping_zipcode ?? '',
                    $order->shipping_address ?? '',

                    // 商品情報
                    $item->name ?? '',
                    $quantity,
                    $basePrice,
                    $shippingFee,
                    $basePrice * $quantity,
                    $fee,
                    $expectedTotal,

                    // 金額計算
                    $originalPriceWithShipping,
                    $campaignPriceWithShipping,
                    $couponPriceWithShipping,
                    $lowestUnitPrice,
                    $appliedLabel
                ]);
            }
        }

        return $rows;
    }
}





