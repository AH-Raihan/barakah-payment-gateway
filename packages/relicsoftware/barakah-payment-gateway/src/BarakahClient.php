<?php

namespace RelicSoftware\BarakahPayment;

use Illuminate\Support\Facades\Http;
use RelicSoftware\BarakahPayment\Exceptions\BarakahConfigurationException;
use RelicSoftware\BarakahPayment\Exceptions\BarakahPaymentException;
use RelicSoftware\BarakahPayment\Responses\PaymentResponse;
use RelicSoftware\BarakahPayment\Responses\PaymentStatusResponse;

class BarakahClient
{
    protected string $baseUrl;
    protected string $apiKey;
    protected int $timeout;
    protected string $mode;

    public function __construct(array $config = [])
    {
        $this->baseUrl = rtrim($config['base_url'] ?? config('barakah.base_url', 'http://127.0.0.1:8000'), '/');
        $this->apiKey = $config['api_key'] ?? config('barakah.api_key', '');
        $this->timeout = (int) ($config['timeout'] ?? config('barakah.timeout', 15));
        $this->mode = $config['mode'] ?? config('barakah.mode', 'sandbox');
    }

    /**
     * Initiate a payment request and return a PaymentResponse object.
     *
     * Example:
     *   $payment = Barakah::createPayment([
     *       'amount' => 500.00,
     *       'description' => 'Semester Fee - Student #1092',
     *       'callback_url' => route('payment.callback'),
     *   ]);
     *   return $payment->redirect();
     */
    public function createPayment(array $params): PaymentResponse
    {
        $this->validateApiKey();

        if (empty($params['amount']) || (float) $params['amount'] <= 0) {
            throw new BarakahPaymentException('Payment amount must be greater than zero.');
        }

        if (empty($params['description'])) {
            throw new BarakahPaymentException('Payment description is required.');
        }

        $url = $this->baseUrl . '/api/v1/payment/create';

        $response = $this->httpClient()
            ->withHeaders([
                'X-API-KEY' => $this->apiKey,
                'Accept' => 'application/json',
            ])
            ->post($url, [
                'amount' => (float) $params['amount'],
                'description' => $params['description'],
                'callback_url' => $params['callback_url'] ?? null,
                'expires_in_minutes' => $params['expires_in_minutes'] ?? 30,
            ]);

        if (!$response->successful()) {
            $json = $response->json() ?? [];
            $msg = $json['message'] ?? 'Failed to initiate Barakah payment (HTTP ' . $response->status() . ')';
            throw new BarakahPaymentException($msg, $response->status(), $json);
        }

        $data = $response->json('data') ?? [];
        return new PaymentResponse($data);
    }

    /**
     * Check the real-time status of a payment.
     */
    public function status(string $paymentId): PaymentStatusResponse
    {
        $url = $this->baseUrl . '/api/v1/payment/' . $paymentId . '/status';

        $response = $this->httpClient()
            ->withHeaders(['Accept' => 'application/json'])
            ->get($url);

        if (!$response->successful()) {
            $json = $response->json() ?? [];
            $msg = $json['message'] ?? 'Failed to retrieve payment status.';
            throw new BarakahPaymentException($msg, $response->status(), $json);
        }

        $data = $response->json('data') ?? [];
        return new PaymentStatusResponse($data);
    }

    /**
     * Quick helper to verify if a payment has been completed.
     */
    public function isPaid(string $paymentId): bool
    {
        return $this->status($paymentId)->isPaid();
    }

    /**
     * Headless API: Step 1 - Send 6-digit SMS OTP to customer's mobile.
     */
    public function sendOtp(string $paymentId, string $phone): array
    {
        $url = $this->baseUrl . '/api/v1/payment/send-otp';

        $response = $this->httpClient()
            ->withHeaders(['Accept' => 'application/json'])
            ->post($url, [
                'payment_id' => $paymentId,
                'phone' => $phone,
            ]);

        if (!$response->successful()) {
            $json = $response->json() ?? [];
            throw new BarakahPaymentException($json['message'] ?? 'Failed to send OTP.', $response->status(), $json);
        }

        return $response->json();
    }

    /**
     * Headless API: Step 2 - Verify the 6-digit SMS OTP code.
     */
    public function verifyOtp(string $paymentId, string $otp): array
    {
        $url = $this->baseUrl . '/api/v1/payment/verify-otp';

        $response = $this->httpClient()
            ->withHeaders(['Accept' => 'application/json'])
            ->post($url, [
                'payment_id' => $paymentId,
                'otp' => $otp,
            ]);

        if (!$response->successful()) {
            $json = $response->json() ?? [];
            throw new BarakahPaymentException($json['message'] ?? 'Invalid OTP code.', $response->status(), $json);
        }

        return $response->json();
    }

    /**
     * Headless API: Step 3 - Execute payment with 5-digit PIN (no password).
     */
    public function execute(string $paymentId, string $pin): array
    {
        $url = $this->baseUrl . '/api/v1/payment/execute';

        $response = $this->httpClient()
            ->withHeaders(['Accept' => 'application/json'])
            ->post($url, [
                'payment_id' => $paymentId,
                'pin' => $pin,
            ]);

        if (!$response->successful()) {
            $json = $response->json() ?? [];
            throw new BarakahPaymentException($json['message'] ?? 'Payment authorization failed.', $response->status(), $json);
        }

        return $response->json();
    }

    /**
     * Ensure API Key is configured before sending authenticated requests.
     */
    protected function validateApiKey(): void
    {
        if (empty($this->apiKey)) {
            throw new BarakahConfigurationException(
                'Barakah API key is missing. Set BARAKAH_API_KEY in your .env file or publish the config with: php artisan vendor:publish --tag=barakah-config'
            );
        }
    }

    /**
     * Base HTTP client instance.
     */
    protected function httpClient()
    {
        return Http::timeout($this->timeout);
    }
}

