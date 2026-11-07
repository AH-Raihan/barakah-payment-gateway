<?php

namespace App\Services;

use App\Models\PaymentRequest;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class PaymentOtpService
{
    public function __construct(protected SmsService $smsService) {}

    /**
     * Generate and dispatch a 6-digit payment verification OTP.
     */
    public function generateOtp(PaymentRequest $payment, string $phoneInput): array
    {
        if ($payment->status !== 'pending') {
            throw new \RuntimeException('This payment request is no longer pending.');
        }

        if ($payment->isExpired()) {
            $payment->update(['status' => 'expired']);
            throw new \RuntimeException('This payment request has expired.');
        }

        // Clean phone/account identifier
        $cleanPhone = preg_replace('/[^0-9]/', '', $phoneInput);

        // Find user by phone or account ID
        $user = User::where(function ($query) use ($cleanPhone, $phoneInput) {
            if (!empty($cleanPhone)) {
                $query->where('phone', $cleanPhone);
            }
            $query->orWhere('account_id', $phoneInput);
        })->first();

        if (!$user) {
            throw new \RuntimeException('No Barakah account found for the given mobile phone number.');
        }

        if ($user->status !== 'active') {
            throw new \RuntimeException('Your account is suspended. Please contact institutional support.');
        }

        $wallet = $user->wallet;
        if (!$wallet || $wallet->status !== 'active') {
            throw new \RuntimeException('Your wallet is inactive.');
        }

        if ((float) $wallet->balance < (float) $payment->amount) {
            throw new \RuntimeException("Insufficient balance. Your available balance is ৳" . number_format($wallet->balance, 2) . " BDT.");
        }

        // Generate 6-digit OTP
        $otp = (string) random_int(100000, 999999);
        $expiryMinutes = config('payment.otp_expiry_minutes', 5);

        // Store OTP in cache
        $cacheKey = 'payment_otp_' . $payment->uuid;
        Cache::put($cacheKey, [
            'user_id' => $user->id,
            'phone' => $user->phone,
            'otp' => $otp,
            'attempts' => 0,
        ], now()->addMinutes($expiryMinutes));

        // Dispatch SMS
        $smsBody = "Barakah Payment OTP is: {$otp} for ৳{$payment->amount} BDT to {$payment->apiClient->name}. Valid for {$expiryMinutes} min. Never share this code.";
        $this->smsService->send($user->phone ?? $cleanPhone, $smsBody);

        $isSandbox = config('payment.mode', 'sandbox') === 'sandbox' || !app()->isProduction();

        Log::info("Payment OTP generated for {$payment->uuid} / User: {$user->id} / OTP: {$otp} (Sandbox: " . ($isSandbox ? 'yes' : 'no') . ")");

        $maskedPhone = $user->phone ? (substr($user->phone, 0, 3) . '****' . substr($user->phone, -3)) : $user->account_id;

        return [
            'success' => true,
            'phone' => $user->phone ?? $user->account_id,
            'masked_phone' => $maskedPhone,
            'user_name' => $user->name,
            'is_sandbox' => $isSandbox,
            'sandbox_otp' => $isSandbox ? $otp : null,
            'message' => "Verification code sent to {$maskedPhone}.",
        ];
    }

    /**
     * Verify the 6-digit OTP.
     */
    public function verifyOtp(PaymentRequest $payment, string $otpInput): User
    {
        $cacheKey = 'payment_otp_' . $payment->uuid;
        $cached = Cache::get($cacheKey);

        if (!$cached) {
            throw new \RuntimeException('Verification code has expired or was not requested. Please request a new code.');
        }

        if ($cached['attempts'] >= 5) {
            Cache::forget($cacheKey);
            throw new \RuntimeException('Too many incorrect verification attempts. Please request a new code.');
        }

        if (trim($otpInput) !== trim($cached['otp'])) {
            $cached['attempts']++;
            Cache::put($cacheKey, $cached, now()->addMinutes(config('payment.otp_expiry_minutes', 5)));
            $remaining = 5 - $cached['attempts'];
            throw new \RuntimeException("Invalid verification code. {$remaining} attempts remaining.");
        }

        // Successfully verified! Store verified token for PIN entry step
        $verifiedKey = 'payment_otp_verified_' . $payment->uuid;
        Cache::put($verifiedKey, $cached['user_id'], now()->addMinutes(10));

        return User::findOrFail($cached['user_id']);
    }

    /**
     * Get the verified user ready for PIN confirmation.
     */
    public function getVerifiedUser(PaymentRequest $payment): User
    {
        $verifiedKey = 'payment_otp_verified_' . $payment->uuid;
        $userId = Cache::get($verifiedKey);

        if (!$userId) {
            throw new \RuntimeException('Please complete mobile and SMS verification first.');
        }

        return User::findOrFail($userId);
    }

    /**
     * Clear OTP session upon payment completion or cancellation.
     */
    public function clearSession(PaymentRequest $payment): void
    {
        Cache::forget('payment_otp_' . $payment->uuid);
        Cache::forget('payment_otp_verified_' . $payment->uuid);
    }
}

