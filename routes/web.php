<?php

use App\Http\Controllers\BorrowingController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('dashboard'));

// All authenticated users
Route::middleware(['auth', 'verified'])->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Products — read-only for all roles, write restricted in controller via policy
    Route::resource('products', ProductController::class);

    // Categories — same pattern
    Route::resource('categories', CategoryController::class);

    // Borrowings — same pattern
    Route::resource('borrowings', BorrowingController::class);
    Route::patch('/borrowings/{borrowing}/return', [BorrowingController::class, 'processReturn'])
        ->name('borrowings.return');

    // Exports
    Route::prefix('exports')->name('exports.')->group(function () {
        Route::get('/products/pdf', [ExportController::class, 'productsPdf'])->name('products.pdf');
        Route::get('/products/excel', [ExportController::class, 'productsExcel'])->name('products.excel');
        Route::get('/borrowings/pdf', [ExportController::class, 'borrowingsPdf'])->name('borrowings.pdf');
        Route::get('/borrowings/excel', [ExportController::class, 'borrowingsExcel'])->name('borrowings.excel');
        Route::get('/borrowings/{borrowing}/slip', [ExportController::class, 'borrowingSlip'])->name('borrowings.slip');
    });

    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.read-all');

    // Profile (Breeze default)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Admin-only: user management
Route::middleware(['auth', 'verified', 'role:admin'])->group(function () {
    Route::resource('users', UserController::class)->except(['create', 'store']);
});

require __DIR__.'/auth.php';
