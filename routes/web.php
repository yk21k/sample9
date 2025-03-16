<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\OrderController;
use App\Http\Controllers\PayPalController;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\CustomerInquiryController;
use App\Http\Controllers\InquiriesController;
use App\Http\Controllers\UsersController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\Seller\OrdersController;
use App\Http\Controllers\Seller\CalendarController;
use App\Http\Controllers\Seller\HolidaySettingController;
use App\Http\Controllers\Seller\ExtraHolidaySettingController;
use App\Http\Controllers\Seller\DesplayController;
use App\Http\Controllers\Auth\RegisterController;

use App\Http\Controllers\ShopProfController;
use App\Http\Controllers\ShopCouponsController;
use App\Http\Controllers\SubOrderController;
use App\Http\Controllers\BotManController;

use App\Models\Order;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::redirect('/', '/home');


Auth::routes();

Route::post('register/pre_check', [App\Http\Controllers\Auth\RegisterController::class, 'pre_check'])->name('register.pre_check');

Route::get('register/verify/{token}', [App\Http\Controllers\Auth\RegisterController::class, 'showForm']);

Route::post('register/main_check', [App\Http\Controllers\Auth\RegisterController::class, 'mainCheck'])->name('register.main.check');

Route::post('register/main_register', [App\Http\Controllers\Auth\RegisterController::class, 'mainRegister'])->name('register.main.registered');




Route::match(['get', 'post'], '/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

Route::match(['get', 'post'], '/botman', [App\Http\Controllers\BotManController::class, 'handle'])->name('handle');

Route::get('/testpage', [App\Http\Controllers\HomeController::class, 'testpage'])->name('testpage');

// テスト
Route::post('/submit', [App\Http\Controllers\HomeController::class, 'submit']);


Route::get('/privacy-policy', [App\Http\Controllers\HomeController::class, 'privacy_policypage'])->name('privacy_policypage');

Route::get('/personal-information', [App\Http\Controllers\HomeController::class, 'personal_information'])->name('personal_information');

Route::get('/terms-of-service', [App\Http\Controllers\HomeController::class, 'terms_of_service'])->name('terms_of_service');

Route::get('/listing_terms', [App\Http\Controllers\HomeController::class, 'listing_terms'])->name('listing_terms');


Route::get('/delete_shop', [App\Http\Controllers\UsersController::class, 'index'])->name('users.delete_shop');

Route::post('/delete_shop', [App\Http\Controllers\UsersController::class, 'termination'])->name('users.delete_shops');



Route::get('products/search', [App\Http\Controllers\ProductController::class, 'search'])->name('products.search');

Route::get('product/{id}', [App\Http\Controllers\ProductController::class, 'detail'])->name('products.detail');

Route::post('product/favorite/{id}', [App\Http\Controllers\ProductController::class, 'productFavo'])->name('products.favorite');


Route::resource('products', ProductController::class);



Route::get('/add-to-cart/{product}', [App\Http\Controllers\CartController::class, 'add'])->name('cart.add')->middleware('auth');

Route::get('/cart', [App\Http\Controllers\CartController::class, 'index'])->name('cart.index')->middleware('auth');

Route::get('/cart/destroy/{itemId}', [App\Http\Controllers\CartController::class, 'destroy'])->name('cart.destroy')->middleware('auth');

Route::get('/cart/update/{itemId}', [App\Http\Controllers\CartController::class, 'update'])->name('cart.update')->middleware('auth');

Route::get('/cart/checkout', [App\Http\Controllers\CartController::class, 'checkout'])->name('cart.checkout')->middleware('auth');

Route::get('/cart/apply-coupon', [App\Http\Controllers\CartController::class, 'applyCoupon'])->name('cart.coupon')->middleware('auth');

Route::get('/cart/apply-shopcoupon', [App\Http\Controllers\CartController::class, 'applyShopCoupon'])->name('cart.shopcoupon')->middleware('auth');

Route::post('/cart/deli-place', [App\Http\Controllers\CartController::class, 'deliPlace'])->name('cart.deli_place')->middleware('auth');


Route::get('/account/{id}', [App\Http\Controllers\AccountController::class, 'index'])->name('account.account')->middleware('auth');

Route::post('/account/{id}', [App\Http\Controllers\AccountController::class, 'updateProf'])->name('account.accounts')->middleware('auth');

Route::post('/account_addresses/{id}', [App\Http\Controllers\AccountController::class, 'saveDeliveryAddress'])->name('account.addresses')->middleware('auth');




Route::get('/shop-prof', [App\Http\Controllers\ShopProfController::class, 'index'])->name('shop_prof')->middleware('auth');



Route::get('/cutomer-inquiry', [App\Http\Controllers\CustomerInquiryController::class, 'inquiryForm'])->name('account.inquiry')->middleware('auth');

Route::post('/cutomer-inquiry', [App\Http\Controllers\CustomerInquiryController::class, 'inquiryAnswer'])->name('account.inquiries')->middleware('auth');

Route::get('/cutomer-answers', [App\Http\Controllers\CustomerInquiryController::class, 'answers'])->name('account.answers')->middleware('auth');



Route::get('inquiries/{id}', [App\Http\Controllers\InquiriesController::class, 'create'])->name('inquiries.create')->middleware('auth');

Route::post('inquiries/{id}', [App\Http\Controllers\InquiriesController::class, 'store'])->name('inquiries.store')->middleware('auth');

Route::get('/inquiries-answers/{id}', [App\Http\Controllers\InquiriesController::class, 'answers'])->name('inquiries.answers')->middleware('auth');


Route::resource('orders', OrderController::class)->only('store')->middleware('auth');


Route::resource('shops', ShopController::class)->middleware('auth');

Route::get('shops/{id}', [App\Http\Controllers\UsersController::class, 'show'])->name('shops.overview');


Route::resource('users',UsersController::class)->middleware('auth');

Route::get('users', [App\Http\Controllers\UsersController::class, 'delete_confirm'])->name('users.delete_confirm');

Route::post('users/{id}', [App\Http\Controllers\UsersController::class, 'destroy'])->name('users.withdraw');


Route::get('paypal/checkout/{order}', [App\Http\Controllers\PayPalController::class, 'getExpressCheckout'])->name('paypal.checkout');


Route::get('paypal/checkout-success/{order}', [App\Http\Controllers\PayPalController::class, 'getExpressCheckoutSuccess'])->name('paypal.success');


Route::get('paypal/checkout-cancel', [App\Http\Controllers\PayPalController::class, 'cancelPage'])->name('paypal.cancel');


Route::group(['prefix' => 'admin'], function () {
    Voyager::routes();

    Route::get('/order/pay/{suborder}', [App\Http\Controllers\SubOrderController::class, 'pay'])->name('order.pay');

    Route::get('/shop-coupon-create', [App\Http\Controllers\ShopCouponsController::class, 'makeCouponPage'])->name('order.make_coupon');

    Route::post('/shop-coupon-create', [App\Http\Controllers\ShopCouponsController::class, 'makeCoupon'])->name('order.make_coupon');


});

Route::group(['prefix' => 'seller', 'middleware' => 'auth', 'as' => 'seller.', 'namespace' => 'App\Http\Controllers\Seller'], function () {

    Route::redirect('/','seller/orders');

    Route::resource('/orders', 'OrdersController');

    Route::get('/orders/delivered/{suborder}', 'OrdersController@markDelivered')->name('order.delivered');

    Route::get('/orders/delivered-accepted/{suborder}', 'OrdersController@markAccepted')->name('order.delivered_accepted');

    Route::get('/orders/delivered-company/{suborder}', 'OrdersController@markDeliveryCom')->name('order.delivered_company');

    Route::get('/orders/delivered-arranged/{suborder}', 'OrdersController@markArranged')->name('order.delivered_arranged');

    Route::get('/orders/shop_mail/{suborder}', 'OrdersController@sendMail')->name('order.shop_mail');

    Route::get('/shop_charts', 'OrdersController@chartPage')->name('order.shop_charts');


    Route::get('/calendar', 'CalendarController@show')->name('seller.calendar');

    //祝日設定
    Route::get('/holiday_setting', 'HolidaySettingController@form')->name("holiday_setting");

    Route::post('/holiday_setting', 'HolidaySettingController@update')->name("update_holiday_setting");

    //臨時営業設定
    Route::get('/extra_holiday_setting', 'ExtraHolidaySettingController@form')->name("extra_holiday_setting");
        
    Route::post('/extra_holiday_setting', 'ExtraHolidaySettingController@update')->name("update_extra_holiday_setting");

    Route::get('/shop_desplay', 'DesplayController@index')->name('shop_desplay');

    Route::post('/shop_desplay', 'DesplayController@saveSelect')->name('select_desplay');

    Route::post('/delete_shop_desplay', 'DesplayController@deleteSelect')->name('delete_desplay');


});



Route::get('/test', function(){
    $o = Order::find(144);

    $o->generateSubOrders();
});
