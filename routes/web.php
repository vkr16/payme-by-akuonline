<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\BillController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PaymentMethodController;
use Illuminate\Support\Facades\Route;

// Public Guest & Landing Routes
Route::get('/', function () {
    return view('landing');
})->name('home');

Route::get('/design-guide', function () {
    return view('design-guide');
})->name('design.guide');

// Public Bill & Payment Interaction Routes
Route::get('/b/{slug}', [BillController::class, 'show'])->name('bills.show');
Route::post('/b/{slug}/calculate', [BillController::class, 'calculateSelection'])->name('bills.calculate');
Route::post('/b/{slug}/claim', [BillController::class, 'claimPayment'])->name('bills.claim');

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

    // Host Approval / Confirmation for Claims
    Route::post('/b/{slug}/claims/batch-confirm', [BillController::class, 'batchConfirmClaims'])->name('bills.claims.batch_confirm');
    Route::post('/b/{slug}/claims/{claimId}/confirm', [BillController::class, 'confirmClaim'])->name('bills.claims.confirm');
    Route::delete('/b/{slug}/claims/{claimId}/reject', [BillController::class, 'rejectClaim'])->name('bills.claims.reject');

    // Manajemen Metode Pembayaran (QRIS & Rekening Bank)
    Route::get('/payment-methods', [PaymentMethodController::class, 'index'])->name('payment_methods.index');
    Route::post('/payment-methods/banks', [PaymentMethodController::class, 'storeBank'])->name('payment_methods.banks.store');
    Route::put('/payment-methods/banks/{id}', [PaymentMethodController::class, 'updateBank'])->name('payment_methods.banks.update');
    Route::delete('/payment-methods/banks/{id}', [PaymentMethodController::class, 'destroyBank'])->name('payment_methods.banks.destroy');
    Route::post('/payment-methods/banks/{id}/default', [PaymentMethodController::class, 'setDefaultBank'])->name('payment_methods.banks.set_default');

    Route::post('/payment-methods/qris', [PaymentMethodController::class, 'storeQris'])->name('payment_methods.qris.store');
    Route::put('/payment-methods/qris/{id}', [PaymentMethodController::class, 'updateQris'])->name('payment_methods.qris.update');
    Route::delete('/payment-methods/qris/{id}', [PaymentMethodController::class, 'destroyQris'])->name('payment_methods.qris.destroy');
    Route::post('/payment-methods/qris/{id}/default', [PaymentMethodController::class, 'setDefaultQris'])->name('payment_methods.qris.set_default');
});
