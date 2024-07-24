<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Observers\ShopObserver;
use App\Observers\CustomerInquiryObserver;
use App\Models\Shop;
use App\Models\CustomerInquiry;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Shop::observe(ShopObserver::class);
        CustomerInquiry::observe(CustomerInquiryObserver::class);
        
    }
}
