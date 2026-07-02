<?php

use App\Http\Controllers\BorrowingController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn() => redirect()->route('dashboard'));

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
