<?php

use App\Http\Controllers\Seller\ShopVideoController;


Route::prefix('seller')
    ->name('seller.')
    ->group(function () {


    /*
    |--------------------------------------------------------------------------
    | Shop Videos
    |--------------------------------------------------------------------------
    */

    Route::resource(
        'shop-videos',
        ShopVideoController::class
    )
    ->only([
        'index',
        'create',
        'store',
        'show',
    ]);


    /*
    |--------------------------------------------------------------------------
    | 動画編集
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/shop-videos/{shopVideo}/edit',
        [ShopVideoController::class, 'edit']
    )->name(
        'shop-videos.edit'
    );

    Route::put(
        '/shop-videos/{shopVideo}',
        [ShopVideoController::class, 'update']
    )->name(
        'shop-videos.update'
    );

    Route::post(
        '/shop-videos/{shopVideo}/process',
        [ShopVideoController::class, 'process']
    )->name('shop-videos.process');

    Route::post(
        '/shop-videos/{shopVideo}/seller-review/approve',
        [ShopVideoController::class, 'approveSellerReview']
    )->name(
        'shop-videos.seller-review.approve'
    );




    /*
    |--------------------------------------------------------------------------
    | Preview Test
    |--------------------------------------------------------------------------
    */

    Route::get(
        'shop-videos/{shopVideo}/preview-test',
        [ShopVideoController::class, 'previewTest']
    )->name(
        'shop-videos.preview-test'
    );


    Route::get(
        'shop-videos/{shopVideo}/preview-test2',
        [ShopVideoController::class, 'previewUrl']
    )->name(
        'shop-videos.preview-test2'
    );

});