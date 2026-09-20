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
