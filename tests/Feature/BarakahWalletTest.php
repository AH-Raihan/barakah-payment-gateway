<?php

use App\Models\ApiClient;
use App\Models\PaymentRequest;
use App\Models\User;
use App\Models\Wallet;
use App\Services\WalletService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

test('landing page loads successfully with halal institutional branding', function () {
    $response = $this->get('/');
    $response->assertStatus(200);
    $response->assertSee('Barakah');
    $response->assertSee('Your Institutional Financial Wallet');
    $response->assertSee('Relic Software');
});

test('user can register and wallet is automatically initialized with zero balance', function () {
    $response = $this->post('/register', [
        'name' => 'Mohammad Rahim',
        'email' => 'rahim@institute.edu.bd',
        'phone' => '01799887766',
        'password' => 'Secret123',
        'password_confirmation' => 'Secret123',
    ]);

    $response->assertRedirect(route('pin.setup'));

    $user = User::where('email', 'rahim@institute.edu.bd')->first();
    expect($user)->not->toBeNull();
    expect($user->account_id)->toStartWith('BRK');
    expect($user->wallet)->not->toBeNull();
    expect((float) $user->wallet->balance)->toBe(0.0);
});

test('user can set initial 5-digit PIN and verify it', function () {
    $user = User::factory()->create([
        'pin' => null,
        'role' => 'user',
        'status' => 'active',
    ]);
    Wallet::create([
        'user_id' => $user->id,
        'balance' => 1000.0,
        'currency' => 'BDT',
        'status' => 'active',
    ]);

    $this->actingAs($user);

    $response = $this->post('/pin/setup', [
        'pin' => '54321',
        'pin_confirmation' => '54321',
    ]);

    $response->assertRedirect(route('dashboard'));
    $user->refresh();
    expect(Hash::check('54321', $user->pin))->toBeTrue();
});

test('user can send money with zero transaction fee to another institutional user', function () {
    $sender = User::factory()->create([
        'pin' => Hash::make('12345'),
        'role' => 'user',
        'status' => 'active',
    ]);
    $senderWallet = Wallet::create([
        'user_id' => $sender->id,
        'balance' => 5000.0,
        'currency' => 'BDT',
        'status' => 'active',
    ]);

    $receiver = User::factory()->create([
        'pin' => Hash::make('12345'),
        'role' => 'user',
        'status' => 'active',
    ]);
    $receiverWallet = Wallet::create([
        'user_id' => $receiver->id,
        'balance' => 1000.0,
        'currency' => 'BDT',
        'status' => 'active',
    ]);

    $this->actingAs($sender);
    session(['pin_verified' => true, 'pin_verified_at' => now()->toISOString()]);

    $response = $this->post('/send', [
        'recipient' => $receiver->account_id,
        'amount' => 1500.0,
        'pin' => '12345',
        'idempotency_key' => 'idem_' . Str::random(16),
        'description' => 'Shared department bill',
    ]);

    $response->assertSessionHasNoErrors();
    $senderWallet->refresh();
    $receiverWallet->refresh();

    // Verify exact balances: 5000 - 1500 = 3500, 1000 + 1500 = 2500 (NO charges/vat)
    expect((float) $senderWallet->balance)->toBe(3500.0);
    expect((float) $receiverWallet->balance)->toBe(2500.0);
});

test('user can cash out via authorized agent with zero charge', function () {
    $user = User::factory()->create([
        'pin' => Hash::make('12345'),
        'role' => 'user',
        'status' => 'active',
    ]);
    $userWallet = Wallet::create([
        'user_id' => $user->id,
        'balance' => 3000.0,
        'currency' => 'BDT',
        'status' => 'active',
    ]);

    $agent = User::factory()->create([
        'pin' => Hash::make('12345'),
        'role' => 'agent',
        'status' => 'active',
    ]);
    $agentWallet = Wallet::create([
        'user_id' => $agent->id,
        'balance' => 10000.0,
        'currency' => 'BDT',
        'status' => 'active',
    ]);

    $this->actingAs($user);
    session(['pin_verified' => true, 'pin_verified_at' => now()->toISOString()]);

    $response = $this->post('/cashout', [
        'agent_account' => $agent->account_id,
        'amount' => 1000.0,
        'pin' => '12345',
        'idempotency_key' => 'idem_' . Str::random(16),
    ]);

    $response->assertSessionHasNoErrors();
    $userWallet->refresh();
    $agentWallet->refresh();

    // User: 3000 - 1000 = 2000, Agent: 10000 + 1000 = 11000 (0 fee)
    expect((float) $userWallet->balance)->toBe(2000.0);
    expect((float) $agentWallet->balance)->toBe(11000.0);
});

test('system rejects transfer when balance is insufficient (cannot drop below 0)', function () {
    $sender = User::factory()->create([
        'pin' => Hash::make('12345'),
        'role' => 'user',
        'status' => 'active',
    ]);
    $senderWallet = Wallet::create([
        'user_id' => $sender->id,
        'balance' => 200.0,
        'currency' => 'BDT',
        'status' => 'active',
    ]);

    $receiver = User::factory()->create([
        'pin' => Hash::make('12345'),
        'role' => 'user',
        'status' => 'active',
    ]);
    $receiverWallet = Wallet::create([
        'user_id' => $receiver->id,
        'balance' => 500.0,
        'currency' => 'BDT',
        'status' => 'active',
    ]);

    $this->actingAs($sender);
    session(['pin_verified' => true, 'pin_verified_at' => now()->toISOString()]);

    $response = $this->post('/send', [
        'recipient' => $receiver->account_id,
        'amount' => 500.0, // more than 200
        'pin' => '12345',
        'idempotency_key' => 'idem_' . Str::random(16),
    ]);

    $senderWallet->refresh();
    expect((float) $senderWallet->balance)->toBe(200.0);
});

test('idempotency key prevents double deduction when network or user clicks multiple times', function () {
    $sender = User::factory()->create(['pin' => Hash::make('12345'), 'role' => 'user', 'status' => 'active']);
    $senderWallet = Wallet::create(['user_id' => $sender->id, 'balance' => 2000.0, 'currency' => 'BDT', 'status' => 'active']);

    $receiver = User::factory()->create(['pin' => Hash::make('12345'), 'role' => 'user', 'status' => 'active']);
    $receiverWallet = Wallet::create(['user_id' => $receiver->id, 'balance' => 0.0, 'currency' => 'BDT', 'status' => 'active']);

    $walletService = app(WalletService::class);
    $idemKey = 'same_key_click_twice_123';

    // 1st transfer
    $tx1 = $walletService->transfer($senderWallet, $receiverWallet, 500.0, 'send_money', 'Test', $idemKey);
    // 2nd transfer with same idempotency key (simulating second click or network retry)
    $tx2 = $walletService->transfer($senderWallet, $receiverWallet, 500.0, 'send_money', 'Test', $idemKey);

    // Both calls must return the same transaction record
    expect($tx1->id)->toBe($tx2->id);

    $senderWallet->refresh();
    $receiverWallet->refresh();

    // Balance should only be deducted ONCE (2000 - 500 = 1500, NOT 1000)
    expect((float) $senderWallet->balance)->toBe(1500.0);
    expect((float) $receiverWallet->balance)->toBe(500.0);
});

test('admin can inject balance and track exact amount added in admin_deposits', function () {
    $admin = User::factory()->create(['role' => 'super_admin', 'status' => 'active']);
    $user = User::factory()->create(['role' => 'user', 'status' => 'active']);
    $userWallet = Wallet::create(['user_id' => $user->id, 'balance' => 100.0, 'currency' => 'BDT', 'status' => 'active']);

    $this->actingAs($admin);
    session(['pin_verified' => true, 'pin_verified_at' => now()->toISOString()]);

    $response = $this->post('/admin/deposits', [
        'user_id' => $user->id,
        'amount' => 50000.0,
        'note' => 'Institutional Semester Stipend',
    ]);

    $response->assertRedirect(route('admin.deposits.index'));

    $userWallet->refresh();
    expect((float) $userWallet->balance)->toBe(50100.0);

    $this->assertDatabaseHas('admin_deposits', [
        'admin_id' => $admin->id,
        'wallet_id' => $userWallet->id,
        'amount' => 50000.0,
        'note' => 'Institutional Semester Stipend',
    ]);
});

test('admin can bulk create employee accounts from batch text or CSV', function () {
    $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);

    $this->actingAs($admin);
    session(['pin_verified' => true, 'pin_verified_at' => now()->toISOString()]);

    $bulkData = "Dr. Hasan, hasan@institute.edu.bd, 01788001122, 10000, user\n" .
                "Prof. Naila, naila@institute.edu.bd, 01788001133, 15000, user";

    $response = $this->post('/admin/users/bulk', [
        'bulk_data' => $bulkData,
        'default_password' => 'Barakah@123',
        'default_pin' => '12345',
    ]);

    $response->assertStatus(200);
    $response->assertSee('Bulk Batch Completed');

    $hasan = User::where('email', 'hasan@institute.edu.bd')->first();
    expect($hasan)->not->toBeNull();
    expect((float) $hasan->wallet->balance)->toBe(10000.0);

    $naila = User::where('email', 'naila@institute.edu.bd')->first();
    expect($naila)->not->toBeNull();
    expect((float) $naila->wallet->balance)->toBe(15000.0);
});

test('external projects can create payment request and user can pay via Barakah Gateway with PIN', function () {
    $merchantUser = User::factory()->create(['role' => 'user', 'status' => 'active']);
    $merchantWallet = Wallet::create(['user_id' => $merchantUser->id, 'balance' => 0.0, 'currency' => 'BDT', 'status' => 'active']);

    $plainApiKey = 'brk_live_testkey1234567890';
    $apiClient = ApiClient::create([
        'name' => 'Campus Book Store App',
        'user_id' => $merchantUser->id,
        'api_key' => Hash::make($plainApiKey),
        'api_secret' => Hash::make('secret'),
        'webhook_url' => 'https://example.com/webhook',
        'status' => 'active',
        'rate_limit' => 60,
    ]);

    // 1. External project calls API to initiate payment
    $apiResponse = $this->withHeaders(['X-API-KEY' => $plainApiKey])
        ->postJson('/api/v1/payment/create', [
            'amount' => 750.0,
            'description' => 'Book Order #2041',
            'callback_url' => 'https://bookstore.test/return',
        ]);

    $apiResponse->assertStatus(201);
    $paymentId = $apiResponse->json('data.payment_id');
    expect($paymentId)->not->toBeNull();

    // 2. Member pays via 3-step OTP and 5-digit PIN verification (bKash/Nagad style, no password)
    $payer = User::factory()->create([
        'phone' => '01812345678',
        'pin' => Hash::make('12345'),
        'role' => 'user',
        'status' => 'active',
    ]);
    $payerWallet = Wallet::create(['user_id' => $payer->id, 'balance' => 1200.0, 'currency' => 'BDT', 'status' => 'active']);

    // Step 2.1: Request SMS OTP
    $sendOtpRes = $this->postJson('/payment/checkout/' . $paymentId . '/send-otp', [
        'phone' => '01812345678',
    ]);
    $sendOtpRes->assertStatus(200);
    $sendOtpRes->assertJson(['success' => true]);
    $otpCode = $sendOtpRes->json('sandbox_otp');
    expect($otpCode)->not->toBeNull();

    // Step 2.2: Verify OTP
    $verifyOtpRes = $this->postJson('/payment/checkout/' . $paymentId . '/verify-otp', [
        'otp' => $otpCode,
    ]);
    $verifyOtpRes->assertStatus(200);
    $verifyOtpRes->assertJson(['success' => true]);

    // Step 2.3: Confirm with 5-digit PIN (NOT account password)
    $payResponse = $this->postJson('/payment/checkout/' . $paymentId . '/pay', [
        'pin' => '12345',
    ]);
    $payResponse->assertStatus(200);
    $payResponse->assertJson(['success' => true]);

    $payerWallet->refresh();
    $merchantWallet->refresh();

    // Payer has 1200 - 750 = 450, Merchant has 750
    expect((float) $payerWallet->balance)->toBe(450.0);
    expect((float) $merchantWallet->balance)->toBe(750.0);

    // 3. Status API reflects payment is complete
    $statusResponse = $this->getJson('/api/v1/payment/' . $paymentId . '/status');
    $statusResponse->assertStatus(200);
    expect($statusResponse->json('data.status'))->toBe('paid');
});

test('api payment gateway supports full headless otp and 5-digit pin checkout', function () {
    $merchantUser = User::factory()->create(['role' => 'admin', 'status' => 'active']);
    $merchantWallet = Wallet::create(['user_id' => $merchantUser->id, 'balance' => 0.0, 'currency' => 'BDT', 'status' => 'active']);

    $plainApiKey = 'brk_api_key_test_headless';
    $client = ApiClient::create([
        'name' => 'Campus Book Store API',
        'user_id' => $merchantUser->id,
        'api_key' => Hash::make($plainApiKey),
        'api_secret' => Hash::make('secret'),
        'webhook_url' => 'https://bookstore.test/webhook',
        'status' => 'active',
        'rate_limit' => 60,
    ]);

    // 1. Create payment
    $createRes = $this->withHeaders(['X-API-KEY' => $plainApiKey])
        ->postJson('/api/v1/payment/create', [
            'amount' => 300.0,
            'description' => 'Stationery Purchase',
        ]);
    $createRes->assertStatus(201);
    $paymentId = $createRes->json('data.payment_id');

    // 2. Customer details
    $customer = User::factory()->create([
        'phone' => '01999888777',
        'pin' => Hash::make('54321'),
        'role' => 'user',
        'status' => 'active',
    ]);
    $customerWallet = Wallet::create(['user_id' => $customer->id, 'balance' => 1000.0, 'currency' => 'BDT', 'status' => 'active']);

    // 3. Send OTP
    $otpRes = $this->postJson('/api/v1/payment/send-otp', [
        'payment_id' => $paymentId,
        'phone' => '01999888777',
    ]);
    $otpRes->assertStatus(200);
    $otpCode = $otpRes->json('data.sandbox_otp');

    // 4. Verify OTP
    $verifyRes = $this->postJson('/api/v1/payment/verify-otp', [
        'payment_id' => $paymentId,
        'otp' => $otpCode,
    ]);
    $verifyRes->assertStatus(200);

    // 5. Execute with 5-digit PIN (no password required)
    $execRes = $this->postJson('/api/v1/payment/execute', [
        'payment_id' => $paymentId,
        'pin' => '54321',
    ]);
    $execRes->assertStatus(200);
    $execRes->assertJson(['success' => true]);

    $customerWallet->refresh();
    $merchantWallet->refresh();

    expect((float) $customerWallet->balance)->toBe(700.0);
    expect((float) $merchantWallet->balance)->toBe(300.0);
});

test('locale switcher updates language to Bengali, Arabic, and English', function () {
    // Switch to Bengali
    $response = $this->get('/locale/bn');
    $response->assertRedirect();
    $response->assertSessionHas('locale', 'bn');

    // Visit home in Bengali
    $homeResponse = $this->withSession(['locale' => 'bn'])->get('/');
    $homeResponse->assertStatus(200);
    $homeResponse->assertSee('Hind Siliguri');
    $homeResponse->assertSee('বারাকাহ');

    // Switch to Arabic
    $arResponse = $this->get('/locale/ar');
    $arResponse->assertRedirect();
    $arResponse->assertSessionHas('locale', 'ar');

    $homeArResponse = $this->withSession(['locale' => 'ar'])->get('/');
    $homeArResponse->assertStatus(200);
    $homeArResponse->assertSee('dir="rtl"', false);
    $homeArResponse->assertSee('بركة');

    // Switch to English
    $enResponse = $this->get('/locale/en');
    $enResponse->assertRedirect();
    $enResponse->assertSessionHas('locale', 'en');

    $homeEnResponse = $this->withSession(['locale' => 'en'])->get('/');
    $homeEnResponse->assertStatus(200);
    $homeEnResponse->assertSee('Barakah');
});

test('home page does not expose test credentials or admin/api internal info', function () {
    $response = $this->get('/');
    $response->assertStatus(200);
    $response->assertDontSee('Test Credentials');
    $response->assertDontSee('user1@barakah.test');
    $response->assertDontSee('admin@barakah.test');
    $response->assertDontSee('0% Charge');
    $response->assertDontSee('Zero-Fee');
});

test('payment demo shop loads successfully', function () {
    $response = $this->get('/payment/demo');
    $response->assertStatus(200);
    $response->assertSee('Campus Institutional Cafeteria');
    $response->assertSee('Pay with Barakah Wallet');
});

test('admin can deposit balance to user account via API and track total balance added', function () {
    $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
    $user = User::factory()->create(['role' => 'user', 'phone' => '01700998877', 'status' => 'active']);
    $wallet = Wallet::create(['user_id' => $user->id, 'balance' => 500.0, 'currency' => 'BDT', 'status' => 'active']);

    // Call API as authenticated admin
    $response = $this->actingAs($admin, 'sanctum')->postJson('/api/v1/admin/deposit', [
        'user' => '01700998877',
        'amount' => 1500.0,
        'note' => 'Faculty Research Grant Q3',
    ]);

    $response->assertStatus(200);
    $response->assertJson([
        'success' => true,
        'data' => [
            'amount_added' => 1500.0,
            'new_balance' => 2000.0,
        ],
    ]);

    $wallet->refresh();
    expect((float) $wallet->balance)->toBe(2000.0);

    // Call API to inspect audit trail and track how much balance was added
    $indexResponse = $this->actingAs($admin, 'sanctum')->getJson('/api/v1/admin/deposits');
    $indexResponse->assertStatus(200);
    $indexResponse->assertJson(['success' => true]);
    expect((float) $indexResponse->json('summary.total_amount_added'))->toBeGreaterThanOrEqual(1500.0);
});
