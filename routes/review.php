<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\ProductReviewController;
use App\Http\Controllers\Admin\AdminProductReviewController;
use App\Http\Controllers\Admin\ShopApplicationReviewController;
use App\Http\Controllers\ShopApplicationController;


Route::get('/invite/staff/{token}', function ($token) {
    dd('REVIEW HIT', $token);
});

Route::prefix('admin')
->middleware(['web','auth','admin', 'reviewer.only'])
->group(function () {
    Route::get('/admin_review', [AdminProductReviewController::class, 'index'])
    ->name('admin.review.index');

    Route::get('/admin_review/{draft}', [AdminProductReviewController::class, 'show'])
        ->name('admin.review.show');

    Route::post(
        '/admin_review/{draft}/approve',
        [AdminProductReviewController::class, 'approve']
    )->name('admin.review.approve');

    Route::post(
        '/admin_review/{draft}/revision',
        [AdminProductReviewController::class, 'revision']
    )->name('admin.review.revision');

    Route::post(
        '/admin_review/{draft}/reject',
        [AdminProductReviewController::class, 'reject']
    )->name('admin.review.reject');

});

Route::prefix('admin')
    ->middleware(['web','auth','admin', 'reviewer.only'])
    ->group(function () {



    // 商品審査
    // Route::get('/review-dashboard', [ProductReviewController::class,'dashboard'])
    //     ->name('admin.review.dashboard');

    // Route::get('product-review', [ProductReviewController::class,'index'])
    //     ->name('product.review');

    // Route::get('product-review/next', [ProductReviewController::class,'next'])
    //     ->name('admin.product-review.next');

    // Route::get('product-review/{id}', [ProductReviewController::class,'show'])
    //     ->name('product.review.show');

    // Route::post('product-review/{id}',[ProductReviewController::class,'review'])->name('product.review.review');

    // Route::post('product-review/{product}/fix', [ProductReviewController::class,'fix'])->name('product.review.fix');

    // Route::post('product-review/{product}/approve', [ProductReviewController::class,'approve'])
    //     ->name('product.approve');

    // Route::post('product-review/{product}/reject', [ProductReviewController::class,'reject'])
    //     ->name('product.reject');

    //店舗審査
    Route::get('shop/review-dashboard', [ShopApplicationReviewController::class,'dashboard'])->name('admin.shop-review.dashboard');
    
    Route::get('/shop-applications', [ShopApplicationReviewController::class, 'index'])->name('admin.shop.review');

    Route::get('/shop-applications/{id}', [ShopApplicationReviewController::class, 'show']);

    Route::post('/shop-applications/{id}/approve', [ShopApplicationReviewController::class, 'approve'])->name('admin.shop.approve');

    Route::post('/shop-applications/{id}/reject', [ShopApplicationReviewController::class, 'reject'])->name('admin.shop.reject'); 

    


});

Route::post('/shop/apply', [ShopApplicationController::class, 'store'])->middleware('auth')->name('shop.apply.form');

/*
|--------------------------------------------------------------------------
| 出品者（審査依頼する側）
|--------------------------------------------------------------------------
*/
Route::middleware(['web','auth'])
    ->group(function () {

    Route::post('product-review/{product}/request',
        [ProductReviewController::class, 'requestReview']
    )->name('product.request');

});
