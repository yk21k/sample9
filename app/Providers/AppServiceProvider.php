<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Observers\ShopObserver;
use App\Observers\CustomerInquiryObserver;
use App\Observers\InquiryObserver;
use App\Models\Shop;
use App\Models\CustomerInquiry;
use App\Models\Inquiries;
use TCG\Voyager\Facades\Voyager;
use Illuminate\Pagination\Paginator;


class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        Voyager::useModel('Category', \App\Models\Categories::class);

    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Shop::observe(ShopObserver::class);
        CustomerInquiry::observe(CustomerInquiryObserver::class);
        Inquiries::observe(InquiryObserver::class);
        Voyager::useModel('Category', \App\Models\Categories::class);
        Paginator::useBootstrap();
        
    }
}
