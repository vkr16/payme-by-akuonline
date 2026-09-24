<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\BillController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

// Public Guest & Landing Routes
Route::get('/', function () {
    return view('landing');
})->name('home');

Route::get('/design-guide', function () {
    return view('design-guide');
})->name('design.guide');

// Public Bill View
Route::get('/b/{slug}', [BillController::class, 'show'])->name('bills.show');

// Guest Only Authentication Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.attempt');

    Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.store');
});

// Authenticated Host Routes
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Buat & Kelola Tagihan Patungan
    Route::get('/bills/create', [BillController::class, 'create'])->name('bills.create');
    Route::post('/bills', [BillController::class, 'store'])->name('bills.store');
    Route::post('/bills/parse-receipt', [BillController::class, 'parseReceipt'])->name('bills.parse_receipt');
});
