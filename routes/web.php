<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Http;

use App\Http\Controllers\OrderController;
use App\Http\Controllers\PayPalController;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\CustomerInquiryController;
use App\Http\Controllers\InquiriesController;
use App\Http\Controllers\UsersController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\AuctionController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PickupCatalogController;
use App\Http\Controllers\PickupCartController;
use App\Http\Controllers\StorePickupPaymentController;
use App\Http\Controllers\PickupCustomerOtpController;
use App\Http\Controllers\PickupOrderController;
use App\Http\Controllers\PickupConfirmController;

use App\Http\Controllers\Seller\OrdersController;
use App\Http\Controllers\Seller\CalendarController;
use App\Http\Controllers\Seller\HolidaySettingController;
use App\Http\Controllers\Seller\ExtraHolidaySettingController;
use App\Http\Controllers\Seller\DesplayController;
use App\Http\Controllers\Seller\ShopSettingController;
use App\Http\Controllers\Seller\StripeConnectController;
use App\Http\Controllers\Seller\AuctionOrderController;
use App\Http\Controllers\Seller\PickupSlotController;
use App\Http\Controllers\Seller\PickupLocationController;
use App\Http\Controllers\Seller\PickupOtpController;
use App\Http\Controllers\Seller\StaffRegisterController;
use App\Http\Controllers\Seller\AdminQrController;


use App\Http\Controllers\Otp\AdminOtpController;
use App\Http\Controllers\Shop\AuthController;

use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\ShopProfController;
use App\Http\Controllers\ShopCouponsController;
use App\Http\Controllers\SubOrderController;
use App\Http\Controllers\BotManController;

use App\Http\Controllers\Admin\StripePayController;

use App\Http\Controllers\Admin\StripeTransferController;
use App\Http\Controllers\StripeOnboardingController;
use App\Http\Controllers\ProductImportController;

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


Route::post('register/pre_check', [App\Http\Controllers\Auth\RegisterController::class, 'pre_check'])->middleware('throttle:1,1')->name('register.pre_check');

Route::get('register/verify/{token}', [App\Http\Controllers\Auth\RegisterController::class, 'showForm']);

Route::post('register/main_check', [App\Http\Controllers\Auth\RegisterController::class, 'mainCheck'])->name('register.main.check');

Route::post('register/main_register', [App\Http\Controllers\Auth\RegisterController::class, 'mainRegister'])->name('register.main.registered');



// 支払い成功後のリダイレクト先URL
Route::get('/payment/success', [App\Http\Controllers\PaymentController::class, 'success'])->name('payment.success');


Route::post('/create-payment-intent', [PaymentController::class, 'createPaymentIntent'])->name('create.payment.intent');



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

// Auction
Route::get('/home/auction', [App\Http\Controllers\AuctionController::class, 'auction_index'])->name('home.auction');

Route::get('/home/auction/show/{auction}', [App\Http\Controllers\AuctionController::class, 'auction_show'])->name('home.auction.show');

Route::get('/home/auction/show/detail/{auction}', [App\Http\Controllers\AuctionController::class, 'auction_detail'])->name('home.auction.detail');


// 入札処理の後、即決金額が設定されていれば決済画面に遷移

Route::post('/auction/{auction}/bid', [AuctionController::class, 'storeBid'])->name('auction.bid.store');

Route::get('/auction/{id}/payment', [AuctionController::class, 'payment'])->name('auction.payment');

// 入札キャンセル用のルート
Route::delete('/auction/bid/{bidId}/cancel', [AuctionController::class, 'cancelBid'])->name('auction.bid.cancel');

Route::post('/add-to-auction-cart/{auction}', [App\Http\Controllers\CartController::class, 'addAuction'])->name('cart.add.auction')->middleware('auth');

Route::post('/auction-charge/{auction}', [App\Http\Controllers\AuctionController::class, 'charge'])->name('auction.charge')->middleware('auth');

Route::get('/auction/payment/success', [AuctionController::class, 'success'])->name('auction.payment.success');

Route::post('/auction/confirm/{auction}', [AuctionController::class, 'confirmDelivery'])->name('auction.delivery.confirm');


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

// Pick Up Product Index
Route::get('/pickup', [App\Http\Controllers\PickupCatalogController::class, 'index'])->name('pickup.catalog.index');
Route::get('/pickup/{pickupProduct}', [App\Http\Controllers\PickupCatalogController::class, 'show'])->name('pickup.catalog.show');

// Pick Up Cart
Route::get('/pickup/cart/index', [App\Http\Controllers\PickupCartController::class, 'index'])->name('pickup.cart.index')->middleware('auth');

Route::post('/pickup/cart/add/{id}', [App\Http\Controllers\PickupCartController::class, 'add'])->name('pickup.cart.add')->middleware('auth');

Route::post('/pickup/cart/update-all-slots', [App\Http\Controllers\PickupCartController::class, 'updateAllSlots'])->name('pickup.cart.updateAllSlots');

Route::get('/pickup/cart/get-available-slots', [App\Http\Controllers\PickupCartController::class, 'getAvailableSlots'])->name('pickup.cart.getAvailableSlots')->middleware('auth'); // 個別商品用

Route::get('/pickup/cart/get-common-slots', [App\Http\Controllers\PickupCartController::class, 'getCommonSlots'])->name('pickup.cart.getCommonSlots'); // 一括用

Route::post('/pickup/cart/remove/{id}', [App\Http\Controllers\PickupCartController::class, 'remove'])->name('pickup.cart.remove')->middleware('auth');
Route::post('/pickup/cart/clear', [App\Http\Controllers\PickupCartController::class, 'clear'])->name('pickup.cart.clear')->middleware('auth');

// バリデーション付きでcheckoutへ進む
Route::post('/pickup/cart/proceed', [App\Http\Controllers\PickupCartController::class, 'proceedToCheckout'])->name('pickup.cart.proceed')->middleware('auth');

// Ajax: 店舗ごとの共通スロット取得
Route::get('/pickup/cart/get-common-slots', [App\Http\Controllers\PickupCartController::class, 'getCommonSlots'])->name('pickup.cart.getCommonSlots');

// 受取先
Route::get('/pickup/cart/pickup-place', [App\Http\Controllers\PickupCartController::class, 'pickUpPlace'])->name('pickup.cart.pickUpPlace');

// 決済画面
Route::get('pickup/cart/checkout', [App\Http\Controllers\PickupCartController::class, 'checkout'])->name('pickup.cart.checkout');

Route::post('/pickup/cart/update', [App\Http\Controllers\PickupCartController::class, 'updatePickupInfo'])
    ->name('pickup.cart.updatePickupInfo');

// --- カート更新用（AJAXでセッションに保存） ---
Route::post('/pickup/cart/save-to-session', [App\Http\Controllers\StorePickupPaymentController::class, 'storeOrder'])
    ->name('pickup.cart.saveToSession');

Route::post('/store-pickup/check-stock', [App\Http\Controllers\StorePickupPaymentController::class, 'checkStock'])
    ->name('store-pickup.check-stock');        

Route::post('pickup/payment/create', [App\Http\Controllers\StorePickupPaymentController::class, 'createPaymentIntent'])->name('store-pickup.payment.create');

Route::post('/stripe/webhook', [App\Http\Controllers\StorePickupPaymentController::class, 'webhook'])->name('stripe.webhook');

Route::post('pickup/order/store', [App\Http\Controllers\StorePickupPaymentController::class, 'storeOrder'])->name('store-pickup.order.store');

Route::get('pickup/payment/success', [App\Http\Controllers\StorePickupPaymentController::class, 'success'])->name('store-pickup.payment.success');

// 購入者側のOTP発行
Route::get('/pickup/otp/pregenerate/', [App\Http\Controllers\PickupCustomerOtpController::class, 'index'])->name('pickup.otp.index')->middleware('auth');

Route::match(['get', 'post'], '/pickup/otp/generate', [App\Http\Controllers\PickupCustomerOtpController::class, 'generate'])->name('pickup.otp.generate')->middleware('auth');

Route::get('/pickup/otp/login', [App\Http\Controllers\PickupCustomerOtpController::class, 'showLoginForm'])->name('pickup.otp.login.form')->middleware('auth');

Route::post('/pickup/otp/verify', [App\Http\Controllers\PickupCustomerOtpController::class, 'verifyOtp'])->name('pickup.otp.verify')->middleware('auth');

Route::post('/pickup/otp/logout/{otp}', [App\Http\Controllers\PickupCustomerOtpController::class, 'logoutOtp'])
    ->name('pickup.otp.logout')->middleware('auth');

// 二段階目OTP表示
Route::get('/pickup/secure/{otp}', [App\Http\Controllers\PickupCustomerOtpController::class, 'showSecurePage'])->name('pickup.otp.secure.show')->middleware('auth');

// 購入者がわの受取確認フォーム
Route::get('/pickup/otp/login', [App\Http\Controllers\PickupCustomerOtpController::class, 'showLoginForm'])
    ->name('pickup.otp.login.form');

Route::post('/pickup/otp/login', [App\Http\Controllers\PickupCustomerOtpController::class, 'verifyOtp'])
    ->name('pickup.otp.login.verify');


Route::post('/pickup/item/{id}/receive', [App\Http\Controllers\PickupConfirmController::class, 'receiveItem'])->name('pickup.item.receive'); 

// 受渡完了後の購入者へのメール
Route::get('/pickup/confirm/{token}', [App\Http\Controllers\PickupConfirmController::class, 'showForm'])
    ->name('pickup.confirm.form'); 

Route::post('/pickup/confirm', [App\Http\Controllers\PickupConfirmController::class, 'submit'])
    ->name('pickup.confirm.submit');    


// 20251005 要否要確認　↓
// Pick Up Reservation
Route::post('/pickup/order/confirm', [App\Http\Controllers\PickupOrderController::class, 'confirm'])->name('pickup.order.confirm');

Route::post('pickup/create-checkout-session', [App\Http\Controllers\PickupOrderController::class, 'createCheckoutSession'])->name('pickup.checkout.session');
Route::get('pickup/checkout-success', [App\Http\Controllers\PickupOrderController::class, 'checkoutSuccess'])->name('pickup.checkout.success');
Route::get('pickup/checkout-cancel', [App\Http\Controllers\PickupOrderController::class, 'checkoutCancel'])->name('pickup.checkout.cancel');
// Stripe Webhook
Route::post('pickup//stripe/webhook', [App\Http\Controllers\PickupOrderController::class, 'handleWebhook'])->name('pickup.stripe.webhook');

Route::post('pickup/checkout/process', [App\Http\Controllers\PickupCheckoutController::class, 'process'])->name('pickup.checkout.process');
Route::get('pickup/checkout/success', [App\Http\Controllers\PickupCheckoutController::class, 'success'])->name('pickup.checkout.success');


// 20251005 要否要確認　↑


Route::get('/account/{id}', [App\Http\Controllers\AccountController::class, 'index'])->name('account.account')->middleware('auth');

Route::post('/account/{id}', [App\Http\Controllers\AccountController::class, 'updateProf'])->name('account.accounts')->middleware('auth');

Route::post('/account_addresses/{id}', [App\Http\Controllers\AccountController::class, 'saveDeliveryAddress'])->name('account.addresses')->middleware('auth');

Route::post('/account_arrival/{id}', [App\Http\Controllers\AccountController::class, 'arrival'])->name('account.arrival')->middleware('auth');

Route::post('/account/addresses/{id}', [App\Http\Controllers\AccountController::class, 'update'])->name('account.addresses.update')->middleware('auth');


Route::get('/shop-prof', [App\Http\Controllers\ShopProfController::class, 'index'])->name('shop_prof');



Route::get('/cutomer-inquiry/{shopId}', [App\Http\Controllers\CustomerInquiryController::class, 'inquiryForm'])->name('customer.inquiry')->middleware('auth');

Route::post('/cutomer-inquiry', [App\Http\Controllers\CustomerInquiryController::class, 'inquiryAnswer'])->name('customer.inquiries')->middleware('auth');

Route::get('/cutomer-answers/{shopId}', [App\Http\Controllers\CustomerInquiryController::class, 'answers'])->name('customer.answers')->middleware('auth');



Route::get('inquiries/{id}', [App\Http\Controllers\InquiriesController::class, 'create'])->name('inquiries.create')->middleware('auth');

Route::post('inquiries/{id}', [App\Http\Controllers\InquiriesController::class, 'store'])->name('inquiries.store')->middleware('auth');

Route::get('/inquiries-answers/', [App\Http\Controllers\InquiriesController::class, 'answers'])->name('inquiries.answers')->middleware('auth');


Route::resource('orders', OrderController::class)->only('store')->middleware('auth');


Route::resource('shops', ShopController::class)->middleware('auth');

Route::get('shops/{id}', [App\Http\Controllers\ShopController::class, 'show'])->name('shops.overview');


Route::resource('users',UsersController::class)->middleware('auth');

Route::get('users', [App\Http\Controllers\UsersController::class, 'delete_confirm'])->name('users.delete_confirm');

Route::post('users/{id}', [App\Http\Controllers\UsersController::class, 'destroy'])->name('users.withdraw');


Route::get('paypal/checkout/{order}', [App\Http\Controllers\PayPalController::class, 'getExpressCheckout'])->name('paypal.checkout');


Route::get('paypal/checkout-success/{order}', [App\Http\Controllers\PayPalController::class, 'getExpressCheckoutSuccess'])->name('paypal.success');


Route::get('paypal/checkout-cancel', [App\Http\Controllers\PayPalController::class, 'cancelPage'])->name('paypal.cancel');


Route::group([
    'prefix' => 'admin',
    // 'middleware' => ['auth', 'otp'],//これだけ追加
    ], function () {
    Voyager::routes();

    Route::get('/order/pay/{suborder}', [App\Http\Controllers\SubOrderController::class, 'pay'])->name('order.pay');

    Route::get('/shop-coupon-create', [App\Http\Controllers\ShopCouponsController::class, 'makeCouponPage'])->name('order.make_coupon_page');

    Route::post('/shop-coupon-create', [App\Http\Controllers\ShopCouponsController::class, 'makeCoupon'])->name('order.make_coupon');

    Route::get('/stripe-transfer/{id}', [StripeTransferController::class, 'transfer'])
     ->name('admin.stripe.transfer');

    Route::get('/pay-to-seller/{id}', [StripePayController::class, 'handle'])->name('admin.pay.to.seller'); 

    Route::post('/shop/send-pickup-confirmation/{item}', [AuthController::class, 'sendPickupConfirmation'])->name('shop.sendPickupConfirmation');



});


// Seller
Route::group(['prefix' => 'seller', 'middleware' => 'auth', 'as' => 'seller.', 'namespace' => 'App\Http\Controllers\Seller'], function () {

    Route::redirect('/','seller/orders');

    Route::resource('/orders', 'OrdersController');

    Route::get('/orders/delivered/{suborder}', 'OrdersController@markDelivered')->name('order.delivered');

    Route::get('/orders/delivered-accepted/{suborder}', 'OrdersController@markAccepted')->name('order.delivered_accepted');

    Route::get('/orders/delivered-company/{suborder}', 'OrdersController@markDeliveryCom')->name('order.delivered_company');

    Route::get('/orders/delivered-arranged/{suborder}', 'OrdersController@markArranged')->name('order.delivered_arranged');

    Route::get('/orders/shop_mail/{suborder}', 'OrdersController@sendMail')->name('order.shop_mail');


    Route::get('/sales-order-invoice2', 'OrdersController@invoice2')->name('order.sales_order_invoice2');
    Route::post('/orders/invoice2', 'OrdersController@invoice2')->name('orders.invoice2');

    Route::get('/sales-order-invoice-slip', 'OrdersController@slip')->name('order.sales_order_invoice_slip');
    Route::get('/sales-order-invoice-slip2', 'OrdersController@slip2')->name('order.sales_order_invoice_slip2');
    

    Route::get('/shop_charts', 'OrdersController@chartPage')->name('order.shop_charts');

    Route::get('/shop_mail', 'OrdersController@shopMailHistory')->name('order.shop_mails_history');


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

    Route::get('/shop_setting', 'ShopSettingController@index')->name('shop.shop_setting');

    Route::post('/shop_setting', 'ShopSettingController@shopUpdate')->name('shop.shop_setting.update');

    Route::get('/seller/orders/export/full', [OrderController::class, 'exportFullOrders'])->name('orders.export.full');


    Route::get('/shop_auction_orders', 'AuctionOrderController@index')->name('auction.shop_auction_orders');

    Route::get('/shop_auction_orders/delivered-accepted/{auctionOrder}', 'AuctionOrderController@markAccepted')->name('auction.delivered_accepted');

    Route::post('/shop_auction_orders/delivered-company/{auctionOrder}', 'AuctionOrderController@markDeliveryCom')->name('auction.delivered_company');

    Route::post('/shop_auction_orders/delivered-arranged/{auctionOrder}', 'AuctionOrderController@markArranged')->name('auction.delivered_arranged');

    Route::post('/shop_auction_orders/delivered/{auctionOrder}', 'AuctionOrderController@markDelivered')->name('auction.delivered');

    Route::get('/pickup-slots', 'PickupSlotController@index')->name('pickup.slots.index');
    Route::get('/pickup-slots/create', 'PickupSlotController@create')->name('pickup.slots.create');
    Route::post('/pickup-slots', 'PickupSlotController@store')->name('pickup.slots.store');
    Route::get('/pickup-slots/{pickupSlot}/edit', 'PickupSlotController@edit')->name('pickup.slots.edit');
    Route::put('/pickup-slots/{pickupSlot}', 'PickupSlotController@update')->name('pickup.slots.update');
    Route::delete('/pickup-slots/{pickupSlot}', 'PickupSlotController@destroy')->name('pickup.slots.destroy');

    // 月単位自動生成
    Route::post('/pickup-slots/generate/{product}', 'PickupSlotController@generateMonthlySlots')->name('pickup.slots.generate');

    // 前月コピー
    Route::post('/pickup-slots/copy-previous-month/{product}', 'PickupSlotController@copyPreviousMonth')->name('pickup.slots.copyPreviousMonth');
    Route::post('/pickup-slots', 'PickupSlotController@store')->name('pickup.slots.store');

    Route::get('pickup-locations/create', 'PickupLocationController@create')->name('pickuplocation.create');
    Route::post('pickup-locations', 'PickupLocationController@store')->name('pickup.locations.store');
    Route::get('/pickup-locations', 'PickupLocationController@index')->name('pickup.locations.index');

    // Shop　Staff Register
    Route::get('pickup./register', 'StaffRegisterController@create')->name('pickup.shop.register');
    Route::post('pickup./register', 'StaffRegisterController@store')->name('pickup.shop.register.store');

    // Pick Up Order seller dashboard
    Route::get('/pickup_shop_orders', 'PickUpOrderController@index')->name('pickup.shop_pickup_orders');

    // Pick Up Order seller dashboard CSV
    Route::post('/pickup/csv', 'PickUpOrderController@exportCsv')->name('shop.pickup.csv');

    // ▼ 毎日更新される QR コードページ
    Route::get('/staff-qr', 'AdminQrController@show')->name('admin.staff.qr');

});


// Shop　Staff をQRでたどり着けるようにする
Route::prefix('shop')->group(function () {
    // 🔐 ログイン関連
    Route::get('/shop_staff/login/{token}', [AuthController::class, 'showLoginForm'])->name('shop_staff.login');
    Route::post('/shop_staff/login', [AuthController::class, 'login'])->name('shop.login.post');
    Route::post('/shop_staff/logout', [AuthController::class, 'logout'])->name('shop_staff.logout');

    // 🏠 ダッシュボード関連（認証必須）


    // ダッシュボード（認証必須）shop.verifyOtp
    Route::middleware('auth:shop_staff')->group(function () {
        Route::get('/staff/dashboard', [AuthController::class, 'dashboard'])->name('shop.dashboard');

        // OTP 検証（POST）
        Route::post('/verify-otp', [AuthController::class, 'verifyOtpStaff'])->name('shop.verifyOtp');

        // 受け渡し担当者記録
        Route::post('/staff/order/{item}/person-in-charge', [AuthController::class, 'personInCharge'])
            ->name('staff.order.person_in_charge');
    });
    
    
});


Route::get('/dashboard', function () {
    return view('dashboard'); // 適切なビューやコントローラーに変更
})->name('dashboard');

// 認証必要なルートグループ
Route::middleware(['web', 'auth'])->group(function () {
    Route::get('/stripe/connect', [StripeConnectController::class, 'redirectToStripe'])->name('stripe.connect');
});

// コールバックルートは auth なしに分離
Route::middleware('web')->group(function () {
    Route::get('/stripe/oauth/callback', [StripeConnectController::class, 'handleCallback'])->name('stripe.callback');
});



Route::middleware(['auth'])->group(function () {
    Route::get('/stripe/onboarding', [StripeOnboardingController::class, 'redirectToStripe'])->name('stripe.onboarding');
    Route::get('/stripe/onboarding/refresh', function () {
        return redirect()->route('stripe.onboarding')->with('message', '再度Stripeに接続してください。');
    });
    Route::get('/stripe/onboarding/complete', function () {
        return redirect()->route('dashboard')->with('message', 'Stripe連携が完了しました！');
    });
});

Route::middleware(['auth'])->group(function () {
    Route::get('/admin/product/import', [App\Http\Controllers\ProductImportController::class, 'showForm'])->name('products.import.form');
    Route::post('/admin/product/import', [App\Http\Controllers\ProductImportController::class, 'import'])->name('products.import');
});

use App\Models\FAQ;
use BotMan\BotMan\BotManFactory;
use BotMan\BotMan\Drivers\DriverManager;

Route::match(['get','post'], '/botman', function () {
    handleBotman('customer');
});

Route::match(['get','post'], '/botman-seller', function () {
    handleBotman('seller');
});

function handleBotman($target)
{
    $config = [];
    DriverManager::loadDriver(\BotMan\Drivers\Web\WebDriver::class);
    $botman = BotManFactory::create($config);

    $botman->hears('{message}', function ($bot, $message) use ($target) {
        $faqs = FAQ::where('is_approved', true)
                    ->where('target', $target)
                    ->get();

        foreach ($faqs as $faq) {
            $keywords = preg_split('/[\s,]+/u', $faq->keywords, -1, PREG_SPLIT_NO_EMPTY);
            foreach ($keywords as $keyword) {
                if (mb_strpos($message, $keyword) !== false) {
                    $bot->reply($faq->answer);
                    return;
                }
            }
            if (mb_strpos($message, $faq->question) !== false) {
                $bot->reply($faq->answer);
                return;
            }
        }

        $bot->reply('すみません、よく分かりませんでした。FAQページをご覧ください。');
    });

    $botman->listen();
}


// Route::match(['get','post'], '/botman', function () {
//     $config = [];
//     DriverManager::loadDriver(\BotMan\Drivers\Web\WebDriver::class);
//     $botman = BotManFactory::create($config);

//     $botman->hears('{message}', function ($bot, $message) {
//         $faqs = FAQ::where('is_approved', true)->get();

//         foreach ($faqs as $faq) {
//             // keywords を配列化（カンマ or スペース区切り対応）
//             $keywords = preg_split('/[\s,]+/u', $faq->keywords, -1, PREG_SPLIT_NO_EMPTY);

//             foreach ($keywords as $keyword) {
//                 if (mb_strpos($message, $keyword) !== false) {
//                     $bot->reply($faq->answer);
//                     return; // 最初に見つかったものを返答
//                 }
//             }

//             // 質問文も部分一致チェック
//             if (mb_strpos($message, $faq->question) !== false) {
//                 $bot->reply($faq->answer);
//                 return;
//             }
//         }

//         // どれにもマッチしなかった場合
//         $bot->reply('すみません、よく分かりませんでした。FAQページをご覧ください。もしくは、「購入」などカギ括弧なしで入力ください');
//     });

//     $botman->listen();
// });


Route::get('/test', function(){
    $o = Order::find(144);

    $o->generateSubOrders();
});
