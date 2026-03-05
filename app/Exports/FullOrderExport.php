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

use App\Models\TaxRate;

class FullOrderExport implements FromCollection
{
    public function collection()
    {
        $today = Carbon::today();
        $rows = collect();
        $rows->push(['出力日: ' . now()->format('Y-m-d')]);

        $user = auth()->user();

        // 🔹 店舗取得（1回のみ）
        $shop = Shop::where('user_id', $user->id)->first();
        $isTaxableBusiness = !empty($shop?->invoice_number);

        // 🔹 税率取得（1回のみ）
        $taxRate = TaxRate::current()?->rate ?? 0;

        // 🔹 手数料設定取得（1回のみ）
        $commission = Commition::first();
        $feeRate = $commission->rate ?? 0;
        $feeFixed = $commission->fixed ?? 0;

        // ✅ ヘッダー分岐
        if ($isTaxableBusiness) {
            $rows->push([
                'オーダーNo.', 'ID', '状況', '注文日',
                '宛名', '電話番号', '郵便番号', '宛先',
                '事業者区分',
                '商品名', '個数',
                '通常価格(配送料なし)',
                '消費税(商品分)',
                '配送料',
                '消費税(送料分)',
                '手数料対象額', '支払手数料',
                '入金額(予定も含む)',
                '通常価格(配送料込)',
                'キャンペーン価格',
                'クーポン価格',
                '最終価格', '適用'
            ]);
        } else {
            $rows->push([
                'オーダーNo.', 'ID', '状況', '注文日',
                '宛名', '電話番号', '郵便番号', '宛先',
                '事業者区分',
                '商品名', '個数',
                '通常価格(配送料なし)',
                '配送料',
                '手数料対象額', '支払手数料',
                '入金額(予定も含む)',
                '通常価格(配送料込)',
                'キャンペーン価格',
                'クーポン価格',
                '最終価格', '適用'
            ]);
        }

        // 🔹 SubOrder取得（eager load）
        $subOrders = SubOrder::with(['order', 'items'])
            ->where('seller_id', $user->id)
            ->get();

        foreach ($subOrders as $subOrder) {

            foreach ($subOrder->items as $item) {

                $quantity = $item->pivot->quantity ?? 1;
                $basePrice = (float) $item->price;
                $shippingFee = (float) $item->shipping_fee;

                $originalPriceWithShipping = $basePrice + $shippingFee;

                // 🔹 手数料
                $fee = (int) ($basePrice * $quantity * $feeRate + $feeFixed);

                // 🔹 税計算（課税業者のみ）
                $productTax = 0;
                $shippingTax = 0;

                if ($isTaxableBusiness) {
                    $productTax = floor($basePrice * $quantity * $taxRate);
                    $shippingTax = floor($shippingFee * $quantity * $taxRate);
                }

                $lowestUnitPrice = $originalPriceWithShipping;

                $expectedTotal = ceil(
                    $lowestUnitPrice * $quantity - $fee
                );

                // ✅ 行データ分岐
                if ($isTaxableBusiness) {

                    $rows->push([
                        $subOrder->order->order_number ?? '',
                        $subOrder->id,
                        $subOrder->status,
                        optional($subOrder->created_at)->format('Y-m-d H:i:s'),
                        $subOrder->order->shipping_fullname ?? '',
                        $subOrder->order->shipping_phone ?? '',
                        $subOrder->order->shipping_zipcode ?? '',
                        $subOrder->order->shipping_address ?? '',

                        '課税業者',

                        $item->name ?? '',
                        $quantity,
                        $basePrice,
                        $productTax,
                        $shippingFee,
                        $shippingTax,
                        $basePrice * $quantity,
                        $fee,
                        $expectedTotal,

                        $originalPriceWithShipping,
                        $originalPriceWithShipping,
                        $originalPriceWithShipping,
                        $lowestUnitPrice,
                        '適用なし'
                    ]);

                } else {

                    $rows->push([
                        $subOrder->order->order_number ?? '',
                        $subOrder->id,
                        $subOrder->status,
                        optional($subOrder->created_at)->format('Y-m-d H:i:s'),
                        $subOrder->order->shipping_fullname ?? '',
                        $subOrder->order->shipping_phone ?? '',
                        $subOrder->order->shipping_zipcode ?? '',
                        $subOrder->order->shipping_address ?? '',

                        '非課税業者',

                        $item->name ?? '',
                        $quantity,
                        $basePrice,
                        $shippingFee,
                        $basePrice * $quantity,
                        $fee,
                        $expectedTotal,

                        $originalPriceWithShipping,
                        $originalPriceWithShipping,
                        $originalPriceWithShipping,
                        $lowestUnitPrice,
                        '適用なし'
                    ]);
                }
            }
        }

        return $rows;
    }


}   





