<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Observers\ShopObserver;
use App\Observers\CustomerInquiryObserver;
use App\Observers\InquiryObserver;
use App\Models\Shop;
use App\Models\CustomerInquiry;
use App\Models\Inquiries;

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
        Inquiries::observe(InquiryObserver::class);
        
    }
}
