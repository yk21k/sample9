<?php

namespace App\Observers;

use App\Models\Campaign;
use App\Models\Product;
use Illuminate\Support\Facades\Session;


class CampaignObserver
{
    /**
     * Handle the Campaign "created" event.
     */
    public function created(Campaign $campaign): void
    {
        
    }

    /**
     * Handle the Campaign "updated" event.
     */
    public function updated(Campaign $campaign): void
    {
        // dd($campaign);
        // Product ni taisite
        // dd($campaign->is_active, $campaign->getOriginal('is_active'));
        // dd($campaign->dicount_rate1);
        $campaign_products = Product::where('shop_id', $campaign->shop_id)->get();
        // dd($campaign_products);

        $campaign_stock = Product::where([
                                    ['shop_id', '=', $campaign->shop_id],
                                    ['stock', '<=', '5']
                                    ])->count();
        
        // dd($campaign_stock);


        if($campaign_stock=0)
        {
            // echo print_r('There is no stock required for the campaign');die;
            echo ("Either the inventory required for the campaign is not available or the product is not registered.") ;die;
        }

        foreach($campaign_products as $pro_rate)
            {
                $pro_rate->campaigns_rate1 = $campaign->dicount_rate1;
                $pro_rate->save();
            }
        
    }

    /**
     * Handle the Campaign "deleted" event.
     */
    public function deleted(Campaign $campaign): void
    {
        $campaign_products = Product::where('shop_id', $campaign->shop_id)->get();
        foreach($campaign_products as $pro_rate)
        {
            $pro_rate->campaigns_rate1 = 0;
            $pro_rate->save();
        }
    }

    /**
     * Handle the Campaign "restored" event.
     */
    public function restored(Campaign $campaign): void
    {
        //
    }

    /**
     * Handle the Campaign "force deleted" event.
     */
    public function forceDeleted(Campaign $campaign): void
    {
        //
    }
}
