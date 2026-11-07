<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PaymentRequest;
use App\Services\PaymentGatewayService;
use App\Services\PaymentOtpService;
use App\Services\PinService;
use Illuminate\Http\Request;

class PaymentApiController extends Controller
{
    public function __construct(
        protected PaymentGatewayService $gatewayService,
        protected PaymentOtpService $otpService,
        protected PinService $pinService
    ) {}

    public function create(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1|max:5000000',
            'description' => 'required|string|max:500',
            'callback_url' => 'nullable|url|max:500',
            'expires_in_minutes' => 'nullable|integer|min:5|max:1440',
        ]);

        $apiClient = $request->attributes->get('api_client');

        $payment = $this->gatewayService->createPaymentRequest(
            $apiClient,
            (float) $request->input('amount'),
            $request->input('description'),
            $request->input('callback_url'),
            $request->input('expires_in_minutes', 30)
        );

        $isSandbox = config('payment.mode', 'sandbox') === 'sandbox' || !app()->isProduction();

        return response()->json([
            'success' => true,
            'message' => 'Payment request initiated.',
            'mode' => $isSandbox ? 'sandbox' : 'live',
            'data' => [
                'payment_id' => $payment->uuid,
                'amount' => (float) $payment->amount,
                'currency' => 'BDT',
                'description' => $payment->description,
                'status' => $payment->status,
                'checkout_url' => url('/payment/checkout/' . $payment->uuid),
                'expires_at' => $payment->expires_at->toISOString(),
            ],
        ], 201);
    }

    /**
     * API Step 1: Send SMS OTP to phone.
     */
    public function sendOtp(Request $request)
    {
        $request->validate([
            'payment_id' => 'required|uuid',
            'phone' => 'required|string|min:4|max:25',
        ]);

        $payment = PaymentRequest::where('uuid', $request->input('payment_id'))->first();
        if (!$payment) {
            return response()->json(['success' => false, 'message' => 'Payment request not found.'], 404);
        }

        try {
            $result = $this->otpService->generateOtp($payment, $request->input('phone'));
            return response()->json([
                'success' => true,
                'message' => $result['message'],
                'data' => $result,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * API Step 2: Verify 6-digit OTP code.
     */
    public function verifyOtp(Request $request)
    {
        $request->validate([
            'payment_id' => 'required|uuid',
            'otp' => 'required|digits:6',
        ]);

        $payment = PaymentRequest::where('uuid', $request->input('payment_id'))->first();
        if (!$payment) {
            return response()->json(['success' => false, 'message' => 'Payment request not found.'], 404);
        }

        try {
            $user = $this->otpService->verifyOtp($payment, $request->input('otp'));
            return response()->json([
                'success' => true,
                'message' => 'OTP verified successfully.',
                'data' => [
                    'user_name' => $user->name,
                    'phone' => $user->phone,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * API Step 3: Authorize payment with 5-digit PIN (no password required).
     */
    public function execute(Request $request)
    {
        $request->validate([
            'payment_id' => 'required|uuid',
            'pin' => 'required|digits:5',
        ]);

        $payment = PaymentRequest::with('apiClient.user')->where('uuid', $request->input('payment_id'))->first();
        if (!$payment) {
            return response()->json(['success' => false, 'message' => 'Payment request not found.'], 404);
        }

        if ($payment->status !== 'pending') {
            return response()->json(['success' => false, 'message' => 'Payment is already ' . $payment->status . '.'], 422);
        }

        try {
            // Get verified user from OTP step
            $user = $this->otpService->getVerifiedUser($payment);

            // Verify 5-digit PIN (with automatic rate limiting lockout)
            $this->pinService->verify($user, $request->input('pin'));

            // Process payment transaction
            $transaction = $this->gatewayService->processPayment(
                $payment,
                $user->wallet,
                $request->ip(),
                $request->userAgent()
            );

            // Clear OTP session
            $this->otpService->clearSession($payment);

            return response()->json([
                'success' => true,
                'message' => 'Payment executed successfully.',
                'data' => [
                    'payment_id' => $payment->uuid,
                    'status' => 'paid',
                    'amount' => (float) $payment->amount,
                    'transaction_reference' => $transaction->reference,
                    'completed_at' => now()->toISOString(),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function status($uuid)
    {
        $payment = PaymentRequest::with('transaction')->where('uuid', $uuid)->first();

        if (!$payment) {
            return response()->json([
                'success' => false,
                'message' => 'Payment request not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'payment_id' => $payment->uuid,
                'status' => $payment->status,
                'amount' => (float) $payment->amount,
                'description' => $payment->description,
                'transaction_reference' => $payment->transaction?->reference,
                'paid_at' => $payment->transaction?->completed_at?->toISOString(),
                'expires_at' => $payment->expires_at?->toISOString(),
            ],
        ]);
    }
}
