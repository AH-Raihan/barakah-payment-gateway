<?php

use App\Http\Controllers\Admin\ApiClientController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\DepositController;
use App\Http\Controllers\Admin\TransactionController as AdminTransactionController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\PinController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\TwoFactorController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PaymentGatewayController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\WalletController;
use Illuminate\Support\Facades\Route;

// ─── Public Landing / Welcome ─────────────────────────────────────
Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }
    return view('welcome');
})->name('home');

// ─── Multilingual Locale Switcher ────────────────────────────────
Route::get('/locale/{lang}', function (string $lang) {
    if (in_array($lang, ['en', 'bn', 'ar'], true)) {
        session(['locale' => $lang]);
    }
    return back();
})->name('locale.switch');

// ─── Authentication Routes ─────────────────────────────────────────
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);

    Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [RegisterController::class, 'register']);

    Route::get('/2fa/challenge', [TwoFactorController::class, 'showChallenge'])->name('2fa.challenge');
    Route::post('/2fa/challenge', [TwoFactorController::class, 'verifyChallenge']);
});

Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// ─── PIN Verification (Authenticated) ──────────────────────────────
Route::middleware('auth')->group(function () {
    Route::get('/pin/setup', [PinController::class, 'showSetup'])->name('pin.setup');
    Route::post('/pin/setup', [PinController::class, 'setup']);

    Route::get('/pin/verify', [PinController::class, 'show'])->name('pin.show');
    Route::post('/pin/verify', [PinController::class, 'verify'])->name('pin.verify');
});

// ─── Protected User Wallet Routes (Requires PIN & Active Wallet) ───
Route::middleware(['auth', 'verify_pin', 'wallet_active'])->group(function () {
    // User Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Wallet Operations
    Route::get('/send', [WalletController::class, 'sendView'])->name('wallet.send');
    Route::post('/send', [WalletController::class, 'sendMoney']);
    Route::post('/send/lookup', [WalletController::class, 'lookupRecipient'])->name('wallet.lookup');

    Route::get('/cashout', [WalletController::class, 'cashOutView'])->name('wallet.cashout');
    Route::post('/cashout', [WalletController::class, 'cashOut']);

    Route::get('/qr', [WalletController::class, 'qrView'])->name('wallet.qr');

    // Transactions History & Receipts
    Route::get('/transactions', [TransactionController::class, 'index'])->name('transactions.index');
    Route::get('/transactions/{uuid}', [TransactionController::class, 'show'])->name('transactions.show');

    // User Profile & Settings
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile');
    Route::post('/profile/update', [ProfileController::class, 'updateProfile'])->name('profile.update');
    Route::get('/profile/security', [ProfileController::class, 'security'])->name('profile.security');
    Route::post('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');
    Route::get('/profile/change-pin', [PinController::class, 'showChange'])->name('profile.change-pin');
    Route::post('/profile/update-pin', [PinController::class, 'update'])->name('profile.update-pin');
    Route::get('/profile/about', [ProfileController::class, 'about'])->name('profile.about');

    // Two Factor Authentication
    Route::get('/2fa/setup', [TwoFactorController::class, 'showSetup'])->name('2fa.setup');
    Route::post('/2fa/enable', [TwoFactorController::class, 'enable'])->name('2fa.enable');
    Route::post('/2fa/disable', [TwoFactorController::class, 'disable'])->name('2fa.disable');
});

// ─── Payment Gateway Web Checkout (Like bKash/Nagad/SSL) ───────────
Route::get('/payment/demo', [PaymentGatewayController::class, 'demoShop'])->name('payment.demo');
Route::post('/payment/demo', [PaymentGatewayController::class, 'createDemoPayment'])->name('payment.demo.create');
Route::get('/payment/checkout/{uuid}', [PaymentGatewayController::class, 'checkout'])->name('payment.checkout');
Route::post('/payment/checkout/{uuid}/send-otp', [PaymentGatewayController::class, 'sendOtp'])->name('payment.send-otp');
Route::post('/payment/checkout/{uuid}/verify-otp', [PaymentGatewayController::class, 'verifyOtp'])->name('payment.verify-otp');
Route::post('/payment/checkout/{uuid}/pay', [PaymentGatewayController::class, 'pay'])->name('payment.pay');
Route::get('/payment/checkout/{uuid}/success', [PaymentGatewayController::class, 'success'])->name('payment.success');

// ─── Institutional Admin Panel (Role: admin, super_admin) ──────────
Route::prefix('admin')->name('admin.')->middleware(['auth', 'verify_pin', 'role:admin,super_admin'])->group(function () {
    Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');

    // User Management & Bulk Creation
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
    Route::post('/users', [UserController::class, 'store'])->name('users.store');
    Route::get('/users/bulk', [UserController::class, 'bulkCreateView'])->name('users.bulk');
    Route::post('/users/bulk', [UserController::class, 'bulkCreateProcess'])->name('users.bulk.process');
    Route::get('/users/{user}', [UserController::class, 'show'])->name('users.show');
    Route::post('/users/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggle-status');

    // Institutional Admin Deposits (Add Balance)
    Route::get('/deposits', [DepositController::class, 'index'])->name('deposits.index');
    Route::get('/deposits/create', [DepositController::class, 'create'])->name('deposits.create');
    Route::post('/deposits', [DepositController::class, 'store'])->name('deposits.store');

    // Global Transactions Audit
    Route::get('/transactions', [AdminTransactionController::class, 'index'])->name('transactions.index');
    Route::get('/transactions/{transaction}', [AdminTransactionController::class, 'show'])->name('transactions.show');

    // External Projects API Clients
    Route::get('/api-clients', [ApiClientController::class, 'index'])->name('api-clients.index');
    Route::get('/api-clients/create', [ApiClientController::class, 'create'])->name('api-clients.create');
    Route::post('/api-clients', [ApiClientController::class, 'store'])->name('api-clients.store');
    Route::post('/api-clients/{client}/toggle', [ApiClientController::class, 'toggleStatus'])->name('api-clients.toggle');
});
