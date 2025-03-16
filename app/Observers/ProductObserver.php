<?php

namespace App\Observers;

use App\Models\Product;
use App\Models\PriceHistory;

class ProductObserver
{
    /**
     * Handle the Product "created" event.
     */
    public function created(Product $product): void
    {
        //
    }

    /**
     * Handle the Product "updated" event.
     */
    public function updated(Product $product): void
    {
        // dd($product->isDirty('price'));
        // 価格が変更された場合に履歴を保存
        if ($product->isDirty('price')) {
            $product->generatePriceHistories();
        }elseif($product->isDirty('stock')){
            // dd('Hello');
            $product->generateStockHistories();
        }


    }

    /**
     * Handle the Product "deleted" event.
     */
    public function deleted(Product $product): void
    {
        //
    }

    /**
     * Handle the Product "restored" event.
     */
    public function restored(Product $product): void
    {
        //
    }

    /**
     * Handle the Product "force deleted" event.
     */
    public function forceDeleted(Product $product): void
    {
        //
    }
}
