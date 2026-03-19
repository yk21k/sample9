<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\ProductReviewController;

Route::prefix('admin')
    ->middleware(['web','auth','admin', 'reviewer.only'])
    ->group(function () {

    Route::get('/review-dashboard', [ProductReviewController::class,'dashboard'])
        ->name('admin.review.dashboard');

    Route::get('product-review', [ProductReviewController::class,'index'])
        ->name('product.review');

    Route::get('product-review/next', [ProductReviewController::class,'next'])
        ->name('admin.product-review.next');

    Route::get('product-review/{id}', [ProductReviewController::class,'show'])
        ->name('product.review.show');

    Route::post('product-review/{id}',[ProductReviewController::class,'review']);

    Route::post('product-review/{product}/approve', [ProductReviewController::class,'approve'])
        ->name('product.approve');

    Route::post('product-review/{product}/reject', [ProductReviewController::class,'reject'])
        ->name('product.reject');

});
