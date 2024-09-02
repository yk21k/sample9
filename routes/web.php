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

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

Route::get('/delete_shop', [App\Http\Controllers\UsersController::class, 'index'])->name('users.delete_shop');

Route::post('/delete_shop', [App\Http\Controllers\UsersController::class, 'termination'])->name('users.delete_shop');


Route::get('products/search', [App\Http\Controllers\ProductController::class, 'search'])->name('products.search');

Route::resource('products', ProductController::class);


Route::get('/add-to-cart/{product}', [App\Http\Controllers\CartController::class, 'add'])->name('cart.add')->middleware('auth');

Route::get('/cart', [App\Http\Controllers\CartController::class, 'index'])->name('cart.index')->middleware('auth');

Route::get('/cart/destroy/{itemId}', [App\Http\Controllers\CartController::class, 'destroy'])->name('cart.destroy')->middleware('auth');

Route::get('/cart/update/{itemId}', [App\Http\Controllers\CartController::class, 'update'])->name('cart.update')->middleware('auth');

Route::get('/cart/checkout', [App\Http\Controllers\CartController::class, 'checkout'])->name('cart.checkout')->middleware('auth');

Route::get('/account', [App\Http\Controllers\AccountController::class, 'index'])->name('account.account')->middleware('auth');

Route::post('/account', [App\Http\Controllers\AccountController::class, 'updateProf'])->name('account.account')->middleware('auth');

Route::get('/cutomer-inquiry', [App\Http\Controllers\CustomerInquiryController::class, 'inquiryForm'])->name('account.inquiry')->middleware('auth');

Route::post('/cutomer-inquiry', [App\Http\Controllers\CustomerInquiryController::class, 'inquiryAnswer'])->name('account.inquiry')->middleware('auth');

Route::get('/cutomer-answers', [App\Http\Controllers\CustomerInquiryController::class, 'answers'])->name('account.answers')->middleware('auth');


Route::get('inquiries/{id}', [App\Http\Controllers\InquiriesController::class, 'create'])->name('inquiries.create')->middleware('auth');

Route::post('inquiries/{id}', [App\Http\Controllers\InquiriesController::class, 'store'])->name('inquiries.store')->middleware('auth');

Route::get('/inquiries-answers/{id}', [App\Http\Controllers\InquiriesController::class, 'answers'])->name('inquiries.answers')->middleware('auth');


Route::resource('orders', OrderController::class)->only('store')->middleware('auth');

Route::resource('shops', ShopController::class)->middleware('auth');



Route::resource('users',UsersController::class)->middleware('auth');

Route::get('users', [App\Http\Controllers\UsersController::class, 'delete_confirm'])->name('users.delete_confirm');

Route::post('users/{id}', [App\Http\Controllers\UsersController::class, 'destroy'])->name('users.withdraw');




Route::get('paypal/checkout/{order}', [App\Http\Controllers\PayPalController::class, 'getExpressCheckout'])->name('paypal.checkout');


Route::get('paypal/checkout-success/{order}', [App\Http\Controllers\PayPalController::class, 'getExpressCheckoutSuccess'])->name('paypal.success');


Route::get('paypal/checkout-cancel', [App\Http\Controllers\PayPalController::class, 'cancelPage'])->name('paypal.cancel');




Route::group(['prefix' => 'admin'], function () {
    Voyager::routes();
});
