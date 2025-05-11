<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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


    public function generateSubOrders()
    {
        $orderItems = $this->items;
        Log::debug('orderItems:', $orderItems->toArray());
        foreach ($orderItems->groupBy('shop_id') as $shopId => $products) {
            if (is_null($shopId)) {
                Log::warning("shop_id が null の商品をスキップしました。");
                continue;
            }
            // dd($products);
            $shop = Shop::find($shopId);
            if (!$shop) {
                Log::warning("Shop ID {$shopId} が見つかりません。");
                continue;
            }

            DB::beginTransaction();

            try {
                Log::debug('サブオーダー作成前のデータ', [
                    'order_id'    => $this->id,
                    'seller_id'   => $shop->user_id ?? 1,
                    'user_id'     => auth()->id(),
                    'grand_total' => $products->sum('pivot.price'),
                    'item_count'  => $products->count(),
                    'coupon_code' => $this->coupon_code,
                ]);
                // サブオーダーの作成
                $suborder = $this->subOrders()->create([
                    'order_id'     => $this->id,
                    'seller_id'    => $shop->user_id ?? 1,
                    'user_id'      => auth()->id(),
                    'grand_total'  => $products->sum('pivot.price'),
                    'item_count'   => $products->count(),
                    'coupon_code'  => $this->coupon_code,
                ]);

                // サブオーダーIDの確認
                if (!$suborder || !$suborder->id) {
                    Log::error('サブオーダーの作成に失敗しました', ['suborder' => $suborder]);
                    throw new \Exception('サブオーダーの作成に失敗しました。');
                }

                Log::debug("Created SubOrder ID: {$suborder->id}");

                // サブオーダーIDが正しいかを再確認
                Log::debug("Inserting to sub_order_items with sub_order_id: {$suborder->id}");
                foreach ($products as $product) {
                    Log::debug('Inserting to sub_order_items with sub_order_id: ' . $suborder->id);

                    try {
                        $suborder->items()->attach($product->id, [
                            'user_id'   => $suborder->user_id,
                            'price'     => $product->pivot->price,
                            'quantity'  => $product->pivot->quantity,
                        ]);
                    } catch (\Exception $e) {
                        Log::error('アイテムの添付に失敗', [
                            'suborder_id' => $suborder->id,
                            'product_id' => $product->id,
                            'message' => $e->getMessage(),
                        ]);
                    }
                }



                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('サブオーダー作成失敗', [
                    'message' => $e->getMessage(),
                    'trace'   => $e->getTraceAsString(),
                ]);
            }

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