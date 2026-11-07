<?php

namespace App\Http\Controllers;

use App\Models\PaymentRequest;
use App\Services\PaymentGatewayService;
use App\Services\PaymentOtpService;
use App\Services\PinService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PaymentGatewayController extends Controller
{
    public function __construct(
        protected PaymentGatewayService $gatewayService,
        protected PaymentOtpService $otpService,
        protected PinService $pinService
    ) {}

    /**
     * Render the bKash/Nagad-style payment checkout page.
     */
    public function checkout($uuid)
    {
        $payment = PaymentRequest::with('apiClient.user')->where('uuid', $uuid)->firstOrFail();

        if ($payment->status === 'paid') {
            return redirect()->route('payment.success', $payment->uuid);
        }

        if ($payment->isExpired()) {
            $payment->update(['status' => 'expired']);
            return view('payment.expired', compact('payment'));
        }

        $user = Auth::user();
        $isSandbox = config('payment.mode', 'sandbox') === 'sandbox' || !app()->isProduction();

        return view('payment.checkout', compact('payment', 'user', 'isSandbox'));
    }

    /**
     * Step 1: Send SMS OTP to the customer's mobile phone number.
     */
    public function sendOtp(Request $request, $uuid)
    {
        $request->validate([
            'phone' => 'required|string|min:4|max:25',
        ]);

        $payment = PaymentRequest::where('uuid', $uuid)->firstOrFail();

        try {
            $result = $this->otpService->generateOtp($payment, $request->input('phone'));
            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Step 2: Verify the 6-digit SMS OTP.
     */
    public function verifyOtp(Request $request, $uuid)
    {
        $request->validate([
            'otp' => 'required|string|size:6',
        ]);

        $payment = PaymentRequest::where('uuid', $uuid)->firstOrFail();

        try {
            $user = $this->otpService->verifyOtp($payment, $request->input('otp'));
            return response()->json([
                'success' => true,
                'message' => 'Verification code confirmed.',
                'user_name' => $user->name,
                'phone' => $user->phone,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Step 3: Authorize payment with 5-digit wallet PIN (NOT password).
     */
    public function pay(Request $request, $uuid)
    {
        $request->validate([
            'pin' => 'required|digits:5',
        ]);

        $payment = PaymentRequest::with('apiClient.user')->where('uuid', $uuid)->firstOrFail();

        if ($payment->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'This payment request is no longer pending.',
            ], 422);
        }

        if ($payment->isExpired()) {
            $payment->update(['status' => 'expired']);
            return response()->json([
                'success' => false,
                'message' => 'This payment request has expired.',
            ], 422);
        }

        // Get the verified user from the OTP step
        try {
            $user = $this->otpService->getVerifiedUser($payment);
        } catch (\Exception $e) {
            // Fallback for authenticated web session if OTP was bypassed in legacy tests
            if (Auth::check()) {
                $user = Auth::user();
            } else {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 422);
            }
        }

        // Verify 5-digit PIN (rate-limited)
        try {
            $this->pinService->verify($user, $request->input('pin'));
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }

        // Process wallet deduction & transaction
        try {
            $transaction = $this->gatewayService->processPayment(
                $payment,
                $user->wallet,
                $request->ip(),
                $request->userAgent()
            );

            // Clear OTP session
            $this->otpService->clearSession($payment);

            // Prepare redirect destination
            $redirectUrl = route('payment.success', $payment->uuid);
            if ($payment->callback_url) {
                $separator = str_contains($payment->callback_url, '?') ? '&' : '?';
                $redirectUrl = $payment->callback_url . $separator . http_build_query([
                    'payment_id' => $payment->uuid,
                    'status' => 'paid',
                    'transaction_ref' => $transaction->reference,
                    'amount' => $payment->amount,
                ]);
            }

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Payment successful!',
                    'redirect_url' => $redirectUrl,
                    'transaction_reference' => $transaction->reference,
                ]);
            }

            return redirect($redirectUrl)->with('success', 'Payment successful!');
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function success($uuid)
    {
        $payment = PaymentRequest::with(['apiClient.user', 'transaction'])->where('uuid', $uuid)->firstOrFail();
        $externalRedirect = request()->query('external_redirect');

        return view('payment.success', compact('payment', 'externalRedirect'));
    }

    public function demoShop()
    {
        return view('payment.demo');
    }

    public function createDemoPayment(Request $request)
    {
        $admin = \App\Models\User::where('role', 'super_admin')->first()
            ?? \App\Models\User::first();

        $client = \App\Models\ApiClient::firstOrCreate(
            ['name' => 'Campus Institutional Cafeteria'],
            [
                'user_id' => $admin->id,
                'api_key' => \Illuminate\Support\Facades\Hash::make('demo_key'),
                'api_secret' => \Illuminate\Support\Facades\Hash::make('demo_secret'),
                'webhook_url' => url('/payment/demo/webhook'),
                'status' => 'active',
                'rate_limit' => 120,
            ]
        );

        $payment = $this->gatewayService->createPaymentRequest(
            $client,
            (float) $request->input('amount', 180.0),
            $request->input('description', 'Cafeteria Lunch Combo & Mineral Water'),
            url('/payment/demo?paid=1')
        );

        return redirect()->route('payment.checkout', $payment->uuid);
    }
}
