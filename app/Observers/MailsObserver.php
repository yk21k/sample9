<?php

namespace App\Observers;

use App\Models\Mails;
use App\Models\SubOrder;
use App\Models\Shop;
use App\Models\ShopCoupon;
use App\Models\Campaign;
use App\Mail\SendMail;
use App\Mail\SendMailCampaign;
use App\Mail\SendMailGreeting;
use App\Mail\SendMailReceipt;
use Illuminate\Support\Facades\Mail;

use Illuminate\Support\Facades\View;
use Illuminate\Mail\Markdown;

class MailsObserver
{
    /**
     * Handle the Mails "created" event.
     */
    public function created(Mails $mails): void
    {
        // dd($mails);
        if($mails->template == "template1"){

            Mail::to($mails->mail)->send(new SendMail($mails));

        }elseif($mails->template == "template2"){

            Mail::to($mails->mail)->send(new SendMailCampaign($mails));

        }elseif($mails->template == "template3"){

            Mail::to($mails->mail)->send(new SendMailGreeting($mails));

        }elseif($mails->purpose == "want_receipt") {

            // $formail_sub_order = SubOrder::with('items')->where('id', $mails->sub_order_id)->first();

            // sub_order を ID指定で1件だけ取得
            $subOrder = SubOrder::with('invoiceSubOrder_item')->find($mails->sub_order_id);
            $shop = Shop::where('user_id', $mails->shop_id)->first();

            // クーポン情報
            $coupon = !empty($mails->coupon_id) ? ShopCoupon::find($mails->coupon_id) : null;

            // キャンペーン情報（例：キャンペーンIDが保存されている場合）
            $campaign = !empty($mails->campaign_id) ? Campaign::find($mails->campaign_id) : null;

                // Product_id の処理
            $productIds = $subOrder->invoiceSubOrder_item->pluck('product_id')->toArray();
            $productIdString = implode(',', $productIds);

            // 文字列を配列に変換（空要素は除外）
            $productIds = array_filter(
                explode(',', $mails->product_id),
                fn($id) => !empty($id)
            );
            // dd($subOrder->invoiceSubOrder_item);

            // 商品ごとの価格情報を作成
            $itemsWithPricing = [];

            foreach ($subOrder->invoiceSubOrder_item as $item) {
                $product = $item->product;
                $price = $item->price;
                $quantity = $item->quantity;
                $shippingFee = $product->shipping_fee ?? 0;

                // 通常価格（配送料込み）
                $normalPrice = $price;
                $normalSubTotal = $normalPrice * $quantity;
                $normalSubTotal_tax = $normalSubTotal * 0.1;

                // キャンペーン割引
                $campaignPrice = $normalPrice;
                if ($campaign && $campaign->dicount_rate1) {
                    // 1商品のみ適用するならフラグ管理が必要
                    $campaignPrice = ($price * (1 - $campaign->dicount_rate1));
                    $campaignSubTotal = $campaignPrice + (($price + $shippingFee) * ($quantity - 1));
                    $campaignSubTotal_tax = $campaignSubTotal * 0.1;
                }

                // --- クーポン価格　最安は考慮しない、考慮するのは、入力のクーポンだけでいい ---
                $couponPrice = $normalPrice;
                // $couponPrice = null;
                // $matchedCoupons = $coupon->where('product_id', $product->id);
                if($coupon !== null){
                    $matchedCoupons = $coupon->where('product_id', $product->id)->get();

                    if ($matchedCoupons->isNotEmpty()) {
                        $bestCouponPrice = $normalPrice; // 初期値は通常価格

                        foreach ($matchedCoupons as $matchedCoupon) {
                            // クーポンは1商品につき1回適用
                            $tmpCouponPrice = $normalPrice + $matchedCoupon->value;
                            if ($tmpCouponPrice < $bestCouponPrice) {
                                $bestCouponPrice = max(0, $tmpCouponPrice); // マイナスにならないように
                            }
                        }

                        $couponPrice = $bestCouponPrice;
                        $couponSubTotal = $couponPrice + (($price + $shippingFee) * ($quantity - 1));
                        $couponSubTotal_tax = $couponSubTotal * 0.1;

                    }
                }

                // 最小金額を採用
                $finalPrice = min($normalPrice, $campaignPrice, $couponPrice);

                // 消費税
                $tax = floor($finalPrice * 0.1);

                $itemsWithPricing[] = [
                    'product_name'     => $product->name ?? '商品名不明',
                    'price'            => $price,
                    'quantity'         => $quantity,
                    'shipping_fee'     => $shippingFee * $quantity,
                    'normal_price'     => $normalPrice,
                    'campaign_price'   => $campaignPrice ?? null,
                    'coupon_price'     => $couponPrice ?? null,
                    'final_price'      => $finalPrice ?? null,
                    'normal_subtotal'   => $normalSubTotal,
                    'campaign_subtotal' => $campaignSubTotal ?? null,
                    'coupon_subtotal'   => $couponSubTotal ?? null,
                    'normal_subtotal_tax'   => $normalSubTotal_tax,
                    'campaign_subtotal_tax' => $campaignSubTotal_tax ?? null,
                    'coupon_subtotal_tax'   => $couponSubTotal_tax ?? null

                ];
            }

            // dd($coupon->product_id, $product->id);
            // dd($couponPrice, ($price + $shippingFee), ($quantity - 1));
            // dd($normalPrice, $campaignPrice, $couponPrice, $finalPrice);


            // Blade に渡す
            $mails->itemsWithPricing = $itemsWithPricing;

            // DBの既存レコードを更新
            $mails->update([
                'subject' => 'ご購入レシートのお知らせ', // 固定でも良い
                'body'    => '', 
                'items_with_pricing' => $itemsWithPricing, // Eloquent が自動で json に変換
            ]);
            // dd($mails);
            
            \Log::info($itemsWithPricing);
            \Log::info($mails);

            Mail::to($mails->mail)->send(
                new SendMailReceipt(
                    $mails,
                    $subOrder,
                    $shop,
                    $coupon,
                    $campaign,
                    $productIds,
                    $subOrder->invoiceSubOrder_item,
                    $itemsWithPricing
                )
            );
            
            // 運営宛
            Mail::to('sample9@admin.com')->send(
                new SendMailReceipt(
                    $mails,
                    $subOrder,
                    $shop,
                    $coupon,
                    $campaign,
                    $productIds,
                    $subOrder->invoiceSubOrder_item,
                    $itemsWithPricing
                )
            );

            // dd($mails->itemsWithPricing);

            // dd($mails, $subOrder, $shop, $coupon, $campaign, $productIds, $subOrder->invoiceSubOrder_item);
            // dd($subOrderItems);



            // Observer または Controller 側
            // $itemsWithPricing = json_decode($mails->items_with_pricing, true) ?? [];
            // $itemsWithPricing = $mails->items_with_pricing ?? [];
            $itemsWithPricing = is_array($mails->items_with_pricing)
            ? $mails->items_with_pricing
            : json_decode($mails->items_with_pricing, true) ?? [];


            // Mailable インスタンスを作成
            $mailInstance = new SendMailReceipt(
                $mails, 
                $subOrder, 
                $shop, 
                $coupon, 
                $campaign, 
                $productIds,
                $subOrder->invoiceSubOrder_item, 
                $itemsWithPricing // 配列をそのまま渡す
            );

            // 件名は envelope() から取得
            $subject = $mailInstance->envelope()->subject;

            // Markdown をレンダリングして HTML に変換
            $markdown = new Markdown(view(), config('mail.markdown'));
            $body = $markdown->render('mail.order.shop-receipt', [
                'mails'              => $mails,
                'subOrder'           => $subOrder,
                'shop'               => $shop,
                'coupon'             => $coupon,
                'campaign'           => $campaign,
                'productIds'         => $productIds,
                'items'              => $subOrder->invoiceSubOrder_item,
                'items_with_pricing' => $itemsWithPricing,
            ]);
        
            // Mail::to($mails->mail)->send($mailInstance);
            // Mail::to('sample9@admin.com')->send($mailInstance);


        }else{

            Mail::to($mails->mail)->send(new SendMailGreeting($mails));

        }
    }

    /**
     * Handle the Mails "updated" event.
     */
    // public function updated(Mails $mails)
    // {
    //     // 以前の値が null なら新規とみなす
    //     if ($mails->getOriginal('id') === null) {
    //         dd('mail just created');
    //     } else {
    //         dd('mail updated');
    //     }
    // }


    /**
     * Handle the Mails "deleted" event.
     */
    public function deleted(Mails $mails): void
    {
        //
    }

    /**
     * Handle the Mails "restored" event.
     */
    public function restored(Mails $mails): void
    {
        //
    }

    /**
     * Handle the Mails "force deleted" event.
     */
    public function forceDeleted(Mails $mails): void
    {
        //
    }
}
