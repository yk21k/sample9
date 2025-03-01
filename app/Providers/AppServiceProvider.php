<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Observers\ShopObserver;
use App\Observers\CustomerInquiryObserver;
use App\Observers\InquiryObserver;
use App\Observers\DeleteShopObserver;
use App\Observers\CampaignObserver;
use App\Observers\DesplayObserver;
use App\Observers\MailsObserver;
use App\Observers\ProductObserver;

use App\Models\Shop;
use App\Models\CustomerInquiry;
use App\Models\Inquiries;
use App\Models\DeleteShop;
use App\Models\ShopCoupon;
use App\Models\Campaign;
use App\Models\Desplay;
use App\Models\Mails;
use App\Models\Product;

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
        Voyager::useModel('Menu', \App\Models\Menu::class);

    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Desplay::observe(DesplayObserver::class);
        Campaign::observe(CampaignObserver::class);
        Shop::observe(ShopObserver::class);
        CustomerInquiry::observe(CustomerInquiryObserver::class);
        Inquiries::observe(InquiryObserver::class);
        DeleteShop::observe(DeleteShopObserver::class);
        Mails::observe(MailsObserver::class);
        Product::observe(ProductObserver::class); 
        Voyager::useModel('Category', \App\Models\Categories::class);
        Voyager::useModel('Menu', \App\Models\Menu::class);
        Paginator::useBootstrap();
        
    }
}
