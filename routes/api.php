<?php

use App\Http\Controllers\Api\AuthApiController;
use App\Http\Controllers\Api\BorrowingApiController;
use App\Http\Controllers\Api\CategoryApiController;
use App\Http\Controllers\Api\DashboardApiController;
use App\Http\Controllers\Api\ProductApiController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->name('api.')->group(function () {

    // Public
    Route::post('/auth/login', [AuthApiController::class, 'login'])->name('auth.login');

    // Authenticated via Sanctum token
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/auth/logout', [AuthApiController::class, 'logout'])->name('auth.logout');

        Route::apiResource('products', ProductApiController::class);
        Route::apiResource('categories', CategoryApiController::class)->only(['index', 'store']);
        Route::apiResource('borrowings', BorrowingApiController::class)->only(['index', 'store', 'show']);
        Route::patch('/borrowings/{borrowing}/return', [BorrowingApiController::class, 'processReturn'])->name('borrowings.return');

        Route::get('/dashboard/summary', [DashboardApiController::class, 'summary'])->name('dashboard.summary');
        Route::get('/dashboard/chart', [DashboardApiController::class, 'chart'])->name('dashboard.chart');
    });
});
