<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AuditLogController;

Route::prefix('admin')
    ->middleware(['admin.user'])
    ->group(function () {

        Route::get(
            '/audit-logs',
            [AuditLogController::class, 'index']
        )->name('admin.audit_logs.index');

        Route::get(
            '/audit-logs/{log}',
            [AuditLogController::class, 'show']
        )->name('admin.audit_logs.show');

    });

Route::get(
    '/seller/products/{product}/timeline',
    [AuditLogController::class, 'productTimeline']
)->name('seller.products.timeline');