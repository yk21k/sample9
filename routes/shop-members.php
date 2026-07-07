<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ShopMemberController;
use App\Http\Controllers\Seller\ProductsController;
use App\Http\Controllers\Seller\ProductApprovalController;
use App\Http\Controllers\Seller\StaffRegiController;
use App\Http\Controllers\Seller\ProductDraftController;
use App\Http\Controllers\Seller\SellerProductReviewController;
use App\Http\Controllers\Seller\ProductEditDraftController;

use App\Http\Controllers\StaffRegisterController;



Route::prefix('seller')->middleware(['auth'])->group(function () {

    // =========================
    // 🔵 店舗メンバー
    // =========================
    Route::middleware('shop.member')->group(function () {

        Route::get('/shops/{shop}/members', [ShopMemberController::class, 'index'])
            ->name('seller.shop_members.index');

        Route::get('/shops/{shop}/members/create', [ShopMemberController::class, 'create'])
            ->name('seller.shop_members.create');

        Route::post('/shops/{shop}/member', [ShopMemberController::class, 'store'])
            ->name('seller.shop_members.store');

        // Route::get('/staff/register/{token}', [StaffRegiController::class, 'showForm'])->name('staff.register.form');

        // Route::get('/staff/register/{token}', function ($token) {
        //     return "招待登録画面 token: " . $token;
        // })->name('staff.register.form');



        // 🔴 manager専用
        Route::middleware('manager')->group(function () {

            Route::delete('/shop-members/{member}', [ShopMemberController::class, 'destroy'])
                ->name('seller.shop_members.destroy');

            Route::post('/shop-members/{member}/resend', [ShopMemberController::class, 'resend'])
                ->name('shop_members.resend');

            Route::post('/shop-members/{member}/expire', [ShopMemberController::class, 'expire'])
                ->name('shop_members.expire');

            Route::get('/shop-members/{member}/logs', [ShopMemberController::class, 'logs'])
                ->name('shop_members.logs'); 

            

            Route::post('/staff/register', [StaffRegiController::class, 'register'])
                ->name('staff.register');            

        });

    });

});

// ShopMember用の招待先URL Route
Route::get('/invite/staff/{token}', [StaffRegisterController::class, 'show'])
->name('invite.staff.register.form');

Route::post('/invite/staff/{token}', [StaffRegisterController::class, 'register'])
->name('invite.staff.register');

// =========================
// 🔥 メール設定
// =========================
Route::post('/members/{member}/set-email', [ShopMemberController::class, 'setEmail'])
    ->name('member.set.email');

// =========================
// 🔥 招待送信
// =========================
Route::post('/members/{member}/invite', [ShopMemberController::class, 'invite'])
    ->name('member.invite');


// ShopMember用のRoute
Route::prefix('seller')->middleware(['auth','shop.member'])->group(function () {

    Route::get('/shops/{shop}/member', [ShopMemberController::class, 'index'])
        ->name('shop_members.index');

    Route::get('/shops/{shop}/member/create', [ShopMemberController::class, 'create'])
        ->name('shop_members.create');

    // Route::post('/shops/{shop}/member', [ShopMemberController::class, 'store'])
    //     ->name('shop_members.store');

    Route::delete('/shop-member/{member}', [ShopMemberController::class, 'destroy'])
        ->name('shop_members.destroy');

    Route::post('/shops/{shop}/attach', [ShopMemberController::class, 'attach'])
    ->name('seller.shop_members.attach');


    Route::post('/members/{member}/resend', [ShopMemberController::class, 'resend'])
    ->name('member.resend');

    Route::post('/members/{member}/expire', [ShopMemberController::class, 'expire'])
    ->name('member.expire');

    Route::delete('/shop-members/{member}', [ShopMemberController::class, 'destroy'])
    ->name('shop_members.destroy');     

});

// =========================
// 🔵 商品
// =========================
Route::middleware(['auth'])
    ->prefix('seller')
    ->name('seller.')
    ->group(function () {

        Route::resource('products', ProductsController::class);

        // Route::post(
        //     '/products/{product}/create-edit-draft',
        //     [ProductsController::class, 'createEditDraft']
        // )
        // ->name('products.create_edit_draft');

        Route::post(
            '/products/{product}/edit-draft',
            [ProductsController::class, 'editDraft']
        )->name('products.edit_draft');



        /*
        |--------------------------------------------------------------------------
        | ownerレビュー
        |--------------------------------------------------------------------------
        */
        Route::get(
            '/product-reviews',
            [SellerProductReviewController::class, 'index']
        )->name('product_reviews.index');

        Route::post(
            '/product-reviews/{product}/approve',
            [SellerProductReviewController::class, 'approve']
        )->name('product_reviews.approve');

        Route::post(
            '/product-reviews/{product}/reject',
            [SellerProductReviewController::class, 'reject']
        )->name('product_reviews.reject');

        /*
        |--------------------------------------------------------------------------
        | owner 審査
        |--------------------------------------------------------------------------
        */
        Route::get(
            '/product-drafts',
            [ProductDraftController::class, 'index']
        )->name('product_drafts.index');

        Route::get(
            '/product-drafts/{draft}/edit',
            [ProductDraftController::class, 'edit']
        )->name('product_drafts.edit');

        Route::put(
            '/product-drafts/{draft}',
            [ProductDraftController::class, 'update']
        )->name('product_drafts.update');

        Route::post(
            '/product-drafts/{draft}/approve',
            [ProductDraftController::class, 'approve']
        )->name('product_drafts.approve');

        Route::post(
            '/product-drafts/{draft}/reject',
            [ProductDraftController::class, 'reject']
        )->name('product_drafts.reject');

        Route::delete(
            '/product-drafts/{draft}',
            [ProductDraftController::class, 'destroy']
        )->name('product_drafts.destroy');


    });