<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

use Auth;

class Order extends Model
{
    use HasFactory;

    protected $guarded = [];
    
    public function items()
    {
        return $this->belongsToMany(Product::class, 'order_items', 'order_id', 'product_id')->withPivot('quantity', 'price');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getShippingFullAddressAttribute()
    {

        return  $this->shipping_fullname."<br>".$this->shipping_address . ', ' . $this->shipping_city . ', ' . $this->shipping_state . ', ' . $this->shipping_zipcode . "<br> phone: " . $this->shipping_phone;
    }

    public function order_item()
    {
       return $this->hasMany(OrderItem::class,  'order_items', 'order_id', 'product_id', 'quantity');
    }

    public function subOrders()
    {
        return $this->hasMany(SubOrder::class);
    }

    public function finalOrders()
    {
        return $this->hasMany(FinalOrder::class);
    }


    public function generateSubOrders()
    {
        $orderItems = $this->items;

        // セッションまたはDBに保存されたクーポンコードを取得
        $couponCodeList = Session::get('applied_coupon_codes', []);
        if (!is_array($couponCodeList)) {
            $couponCodeList = explode(',', $couponCodeList);
        }
        $couponCodeList = array_map('trim', array_filter($couponCodeList));

        foreach ($orderItems->groupBy('shop_id') as $shopId => $products) {

            if (is_null($shopId)) {
                Log::warning("shop_id が null の商品をスキップしました。");
                continue;
            }

            $shop = Shop::find($shopId);
            if (!$shop) {
                Log::warning("Shop ID {$shopId} が見つかりません。");
                continue;
            }

            // 課税判定およびインボイス番号格納
            $invoiceNumber = $shop->invoice_number;
            $isTaxable = !empty($invoiceNumber);

            // ショップ単位のキャンペーン（重複時は割引率最大）
            $campaign = Campaign::where('shop_id', $shopId)
                                ->where('start_date', '<=', now())
                                ->where('end_date', '>=', now())
                                ->orderByDesc('dicount_rate1')
                                ->first();

            DB::beginTransaction();
            try {
                // サブオーダー作成（grand_totalは割引なし初期値0）
                $suborder = $this->subOrders()->create([
                    'order_id'       => $this->id,
                    'seller_id'      => $shop->user_id ?? 1,
                    'user_id'        => auth()->id(),
                    'grand_total'    => 0,
                    'tax_amount'     => 0,
                    'item_count'     => $products->count(),
                    'coupon_code'    => implode(',', $couponCodeList),
                    'is_taxable'     => $isTaxable,
                    'invoice_number' => $invoiceNumber,
                ]);

                $suborderTotal = 0;
                $suborderTax   = 0;

                foreach ($products as $product) {
                    $unitPrice = $product->pivot->price;   // 割引前
                    $quantity  = $product->pivot->quantity;

                    // 商品ごとのクーポン適用（1つだけ）
                    $shopCoupon = ShopCoupon::whereIn('code', $couponCodeList)
                                            ->where('product_id', $product->id)
                                            ->where('expiry_date', '>=', now())
                                            ->first();
                    $couponPrice = $shopCoupon ? max(0, $unitPrice + $shopCoupon->value) : null;

                    // キャンペーン適用（1つだけ）
                    $campaignPrice = $campaign ? floor($unitPrice * (1 - $campaign->dicount_rate1)) : null;

                    // 通常価格・キャンペーン価格・クーポン価格の最小を採用
                    $priceCandidates = [$unitPrice];
                    if ($couponPrice !== null) $priceCandidates[] = $couponPrice;
                    if ($campaignPrice !== null) $priceCandidates[] = $campaignPrice;
                    $discountedUnitPrice = min($priceCandidates);

                    // 割引は1点のみ、残りは通常価格
                    $subtotal = ($quantity > 1 && $discountedUnitPrice < $unitPrice)
                        ? $discountedUnitPrice + $unitPrice * ($quantity - 1)
                        : $discountedUnitPrice * $quantity;

                    // 税額（送料は含まない）
                    $taxRate = TaxRate::current()?->rate ?? 0;
                    if ($isTaxable) {
                        if ($quantity > 1 && $discountedUnitPrice < $unitPrice) {
                            $discountedTax = floor($discountedUnitPrice * $taxRate);
                            $normalTax     = floor($unitPrice * $taxRate) * ($quantity - 1);
                            $tax = $discountedTax + $normalTax;
                        } else {
                            $tax = floor($discountedUnitPrice * $taxRate * $quantity);
                        }
                    } else {
                        $tax = 0;
                    }

                    // 送料（表示用、課税考慮）
                    $shippingFee = (float) $product->shipping_fee;
                    $shippingTotal = $isTaxable
                        ? floor($shippingFee * (1 + $taxRate) * $quantity)
                        : $shippingFee * $quantity;

                    // SubOrderItem 登録
                    $suborder->items()->attach($product->id, [
                        'user_id'         => $suborder->user_id,
                        'price'           => $unitPrice,
                        'quantity'        => $quantity,
                        'discounted_price'=> $discountedUnitPrice,
                        'subtotal'        => $subtotal,
                        'tax_amount'      => $tax,
                        'shipping_fee'    => $shippingTotal,
                        'campaign_id'     => $campaign?->id,
                        'coupon_id'       => $shopCoupon?->id,
                    ]);

                    // SubOrder 合計値計算
                    $suborderTotal += $unitPrice * $quantity; // 割引なし通常価格
                    $suborderTax   += $tax;
                }

                // サブオーダー総額・税額更新（grand_total は割引なし）
                $suborder->update([
                    'grand_total' => $suborderTotal,
                    'tax_amount'  => $suborderTax,
                ]);

                // クーポン紐付け
                if ($shopCoupon) {
                    $suborder->shopCoupons()->syncWithoutDetaching([$shopCoupon->id]);
                }

                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('SubOrder作成失敗', [
                    'message' => $e->getMessage(),
                    'trace'   => $e->getTraceAsString(),
                ]);
            }
        }

        Log::info('generateSubOrders 呼び出し確認');
    }



    public function generateFinalOrderFromCart($cartItems)
    {
        $taxRate = TaxRate::current()?->rate ?? 0;

        Log::info("generateFinalOrderFromCart 呼び出し確認");

        // ショップごとにグループ化
        foreach ($cartItems->groupBy(fn($item) => $item->associatedModel->shop_id) as $shopId => $shopItems) {
            $shop = Shop::find($shopId);
            $isTaxable = !empty($shop->invoice_number);

            // --- キャンペーン取得（重複している場合は最も割引率の高いものを適用）
            $campaign = Campaign::where('shop_id', $shopId)
                ->where('start_date', '<=', now())
                ->where('end_date', '>=', now())
                ->orderByDesc('dicount_rate1')
                ->first();

            $shopSubtotal = 0;
            $shopTax = 0;
            $shopShipping = 0;

            foreach ($shopItems as $item) {
                $product = $item->associatedModel;
                $quantity = $item->quantity;
                $normalPrice = (float) $item->price;
                $shippingFee = (float) $product->shipping_fee;

                // --- クーポン取得（商品ごと、1個だけ適用）
                $coupon = ShopCoupon::where('shop_id', $shopId)
                    ->where('product_id', $product->id)
                    ->where('expiry_date', '>=', now())
                    ->orderByDesc('value') // 割引額が大きいものを優先
                    ->first();

                $couponPrice = $normalPrice;
                $couponApplied = false;
                if ($coupon) {
                    $couponPrice = max(0, $normalPrice + $coupon->value); // valueは負
                    $couponApplied = true;
                }

                // --- キャンペーン価格
                $campaignPrice = $normalPrice;
                if ($campaign) {
                    $campaignPrice = floor($normalPrice * (1 - $campaign->dicount_rate1));
                }

                // --- 割引後の最安値（キャンペーン or クーポン or 通常価格）
                $discountedUnitPrice = min($normalPrice, $couponPrice, $campaignPrice);


                // --- 小計（割引は1個だけ、残りは通常価格）
                if ($quantity > 1 && $discountedUnitPrice < $normalPrice) {
                    $subtotal = $discountedUnitPrice + ($normalPrice * ($quantity - 1));
                } else {
                    $subtotal = $discountedUnitPrice * $quantity;
                }

                Log::info("Test {$coupon->value} | {$normalPrice} | {$couponPrice} | {$campaignPrice} | {$couponApplied}");

                // --- 税額
                if ($isTaxable) {
                    if ($quantity > 1 && $discountedUnitPrice < $normalPrice) {
                        $discountedTax = floor($discountedUnitPrice * $taxRate);
                        $normalTax = floor($normalPrice * $taxRate) * ($quantity - 1);
                        $itemTax = $discountedTax + $normalTax;
                    } else {
                        $itemTax = floor($discountedUnitPrice * $taxRate * $quantity);
                    }
                } else {
                    $itemTax = 0;
                }

                // --- 配送料（数量分 * 税込/税抜）
                $shippingTotal = $isTaxable
                    ? floor($shippingFee * (1 + $taxRate) * $quantity)
                    : $shippingFee * $quantity;

                // --- Shopごとの合計に加算
                $shopSubtotal += $subtotal;
                $shopTax += $itemTax;
                $shopShipping += $shippingTotal;

                // --- FinalOrderItem 保存
                $finalOrder = FinalOrder::firstOrCreate(
                    [
                        'order_id' => $this->id,
                        'shop_id' => $shopId,
                    ],
                    [
                        'is_taxable' => $isTaxable,
                        'subtotal' => 0,
                        'tax_amount' => 0,
                        'shipping_fee' => 0,
                        'total' => 0,
                    ]
                );

                FinalOrderItem::create([
                    'final_order_id' => $finalOrder->id,
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'quantity' => $quantity,
                    'unit_price' => $normalPrice,
                    'subtotal' => $subtotal,
                    'tax_amount' => $itemTax,
                    'shipping_fee' => $shippingTotal,
                ]);

                Log::info("Order {$this->id} | Shop {$shopId} | Product {$product->id} | Qty {$quantity} | Price {$normalPrice} | Discounted {$discountedUnitPrice} | Taxable {$isTaxable} | Tax {$itemTax} | Shipping {$shippingTotal} | Subtotal {$subtotal}");
            }

            // --- FinalOrder 更新
            $finalOrder->update([
                'subtotal' => $shopSubtotal,
                'tax_amount' => $shopTax,
                'shipping_fee' => $shopShipping,
                'total' => $shopSubtotal + $shopTax + $shopShipping,
            ]);

            Log::info("FinalOrder {$finalOrder->id} | Shop {$shopId} | Subtotal {$shopSubtotal} | Tax {$shopTax} | Shipping {$shopShipping} | Total " . ($shopSubtotal + $shopTax + $shopShipping));
        }
    }

    public function favoriteSales()
    {
        return $this->hasMany(FavoritesSaleRate::class);
    }

    public function favoriteRates()
    {
        return $this->hasMany(FavoritesDisplay::class);
    }

    public function generateFavoritesSalesRate()
    {
        // use
        $normSaleUnits2 = OrderItem::select('product_id', DB::raw('SUM(quantity) AS total_q'))->groupBy('product_id')->orderByDesc('total_q')->get();

        $maxUnits2 = (int)$normSaleUnits2->max('total_q');

        $averageRatings = Fovorite::select('product_id', DB::raw('AVG(wants) * 0.4 as average_rating'))->groupBy('product_id')->orderByDesc('average_rating')->get()->toArray();

        // $normSaleUnits_parts = collect($normSaleUnits2)->where('product_id', )->first();

        // dd($normSaleUnits_parts->total_q, $normSaleUnits_parts->total_q/$maxUnits2, $maxUnits2);
        // ---use

        $orderItems = $this->items;
        foreach ($orderItems->groupBy('shop_id') as $shopId => $products) {
            // dd($products);
            foreach($products as $product) {
                $normSaleUnits_parts = collect($normSaleUnits2)->where('product_id', $product->id)->first();

                $norm_sales = $normSaleUnits_parts->total_q* 0.6/$maxUnits2;
                if(null!==(collect($averageRatings))){
                    $norm_rates['average_rating'] = 0;
                }else{
                    $norm_rates = collect($averageRatings)->where('product_id', $product->id)->first();
                    
                }

                $shop = Shop::find($shopId);
                $fovorite = $this->favoriteSales()->create([
                    'order_id'=> $this->id,
                    'shop_id'=> $shop->user_id ?? 1,
                    'product_id'=> $product->id,
                    'fovorite_id'=>"0",
                    'norm_sale'=>$norm_sales,
                    'norm_rate'=>$norm_rates['average_rating']
                ]);
            }    
        }    
    }

    public function generateFavoritesDisplay()
    {
        // use
        $normSaleUnits2 = OrderItem::select('product_id', DB::raw('SUM(quantity) AS total_q'))->groupBy('product_id')->orderByDesc('total_q')->get();

        $maxUnits2 = (int)$normSaleUnits2->max('total_q');

        $averageRatings = Fovorite::select('product_id', DB::raw('AVG(wants) * 0.4 as average_rating'))->groupBy('product_id')->orderByDesc('average_rating')->get()->toArray();

        // ---use
        // dd(collect($averageRatings));
        // dd(null!==(collect($averageRatings)));

        $orderItems = $this->items;
        foreach ($orderItems->groupBy('shop_id') as $shopId => $products) {
            // dd($products);
            foreach($products as $product) {
                $normSaleUnits_parts = collect($normSaleUnits2)->where('product_id', $product->id)->first();

                $norm_sales = $normSaleUnits_parts->total_q* 0.6/$maxUnits2;

                if(null!==(collect($averageRatings))){

                    $norm_rates = collect($averageRatings)->where('product_id', $product->id)->first();
                    if(empty($norm_rates)){
                        $norm_rates['average_rating'] = 0;
                    }

                }else{
                    $norm_rates['average_rating'] = 0;
                }
                // dd($product->id);
                // dd(collect($averageRatings));

                $shop = Shop::find($shopId);

                $pram_fovorite = FavoritesDisplay::where('product_id', $product->id)->latest()->first();

                // dd($pram_fovorite);
                // dd($norm_rates);
                if($pram_fovorite){
                    FavoritesDisplay::where('product_id', $product->id)->update([
                        'order_id'=> $this->id,
                        'norm_sale'=>$norm_sales,
                        'norm_rate'=>$norm_rates['average_rating']
                    ]);                
                }else{
                    $fovorite = $this->favoriteRates()->create([
                    'order_id'=> $this->id,
                    'shop_id'=> $shop->user_id ?? 1,
                    'product_id'=> $product->id,
                    'fovorite_id'=> "0",
                    'norm_sale'=>$norm_sales,
                    'norm_rate'=>$norm_rates['average_rating'],
                    'norm_total'=>"0"
                    ]);    
                }
                $norm_total = $norm_sales + $norm_rates['average_rating'];
                
                FavoritesDisplay::where('product_id', $product->id)->update(['norm_total'=>$norm_total]);
            }    
        }    
    }


}