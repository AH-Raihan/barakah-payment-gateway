<?php

use App\Http\Controllers\Api\AdminDepositApiController;
use App\Http\Controllers\Api\PaymentApiController;
use App\Http\Controllers\Api\WalletApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// ─── External Projects Payment Gateway API (V1) ───────────────────
Route::prefix('v1/payment')->group(function () {
    // External applications create payment requests with their API key
    Route::post('/create', [PaymentApiController::class, 'create'])->middleware('api_client');
    // Step 1: Send SMS OTP
    Route::post('/send-otp', [PaymentApiController::class, 'sendOtp']);
    // Step 2: Verify OTP
    Route::post('/verify-otp', [PaymentApiController::class, 'verifyOtp']);
    // Step 3: Authorize payment with 5-digit PIN (no password required)
    Route::post('/execute', [PaymentApiController::class, 'execute']);
    // Check payment status
    Route::get('/{uuid}/status', [PaymentApiController::class, 'status']);
});

// ─── Admin Balance Injection & Audit API (V1) ─────────────────────
Route::prefix('v1/admin')->middleware(['auth:sanctum'])->group(function () {
    // Add balance to user wallet by API
    Route::post('/deposit', [AdminDepositApiController::class, 'deposit']);
    // View deposit audit logs and total balance added
    Route::get('/deposits', [AdminDepositApiController::class, 'index']);
});

// ─── Authenticated User / Client Wallet API ────────────────────────
Route::prefix('v1/wallet')->middleware(['auth:sanctum'])->group(function () {
    Route::get('/balance', [WalletApiController::class, 'balance']);
    Route::post('/device-token', [\App\Http\Controllers\DeviceTokenController::class, 'store']);
    Route::delete('/device-token', [\App\Http\Controllers\DeviceTokenController::class, 'destroy']);
});
