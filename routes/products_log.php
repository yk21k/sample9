<?php

use Illuminate\Support\Facades\Route;

Route::get(
    '/seller/products/{product}/timeline',
    [ActivityLogController::class, 'productTimeline']
)->name('seller.products.timeline');