<?php

namespace App\Exports;

use App\Models\SubOrder;
use App\Models\Shop;
use App\Models\Campaign;
use App\Models\ShopCoupon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Carbon\Carbon;

class FullOrderExport implements FromCollection
{
    public function collection()
    {
        $today = Carbon::today();
        $rows = collect();

        // ✅ ヘッダー行（1行目）
        $rows->push([
            // 注文情報
            'Order Number', 'SubOrder ID', 'Status',
            'Shipping Name', 'Shipping Phone', 'Zipcode', 'Address',

            // 商品情報
            'Item Name', 'Quantity', 'Base Price', 'Shipping Fee', 'Fee',

            // 価格関連
            '通常価格', 'キャンペーン価格', 'クーポン価格', '最終価格', '適用(２つ目以降は適用なし)'
        ]);

        // ✅ 対象の SubOrder 取得（管理者 or セラー）
        $user = auth()->user();
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
                    $order->shipping_fullname ?? '',
                    $order->shipping_phone ?? '',
                    $order->shipping_zipcode ?? '',
                    $order->shipping_address ?? '',

                    // 商品情報
                    $item->name ?? '',
                    $quantity,
                    $basePrice,
                    $shippingFee,
                    $basePrice * $quantity * 0.1,

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


