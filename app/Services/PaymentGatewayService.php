<?php

namespace App\Services;

use App\Models\ApiClient;
use App\Models\PaymentRequest;
use App\Models\Wallet;
use App\Models\Transaction;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PaymentGatewayService
{
    public function __construct(private WalletService $walletService)
    {
    }

    /**
     * Create a new payment request from an external API client.
     */
    public function createPaymentRequest(
        ApiClient $apiClient,
        float $amount,
        string $description,
        ?string $callbackUrl = null,
        ?int $expiresInMinutes = 30
    ): PaymentRequest {
        if ($amount <= 0) {
            throw new \InvalidArgumentException('Amount must be greater than zero.');
        }

        return PaymentRequest::create([
            'api_client_id' => $apiClient->id,
            'uuid' => Str::uuid()->toString(),
            'amount' => $amount,
            'description' => $description,
            'callback_url' => $callbackUrl ?? $apiClient->webhook_url,
            'status' => 'pending',
            'expires_at' => now()->addMinutes($expiresInMinutes),
        ]);
    }

    /**
     * Process a payment (user pays a payment request).
     */
    public function processPayment(
        PaymentRequest $paymentRequest,
        Wallet $payerWallet,
        ?string $ipAddress = null,
        ?string $userAgent = null
    ): Transaction {
        if ($paymentRequest->status !== 'pending') {
            throw new \RuntimeException('Payment request is no longer pending.');
        }

        if ($paymentRequest->isExpired()) {
            $paymentRequest->update(['status' => 'expired']);
            throw new \RuntimeException('Payment request has expired.');
        }

        // The receiver is the API client's user's wallet
        $receiverWallet = $paymentRequest->apiClient->user->wallet;

        if (!$receiverWallet) {
            throw new \RuntimeException('Merchant wallet not found.');
        }

        // Process the transfer
        $transaction = $this->walletService->transfer(
            $payerWallet,
            $receiverWallet,
            $paymentRequest->amount,
            'payment',
            $paymentRequest->description,
            'PAY-' . $paymentRequest->uuid, // idempotency key
            $ipAddress,
            $userAgent
        );

        // Update payment request
        $paymentRequest->update([
            'status' => 'paid',
            'payer_wallet_id' => $payerWallet->id,
            'transaction_id' => $transaction->id,
        ]);

        // Send webhook notification (async via queue)
        $this->sendWebhook($paymentRequest);

        return $transaction;
    }

    /**
     * Send webhook notification to the API client.
     */
    private function sendWebhook(PaymentRequest $paymentRequest): void
    {
        $callbackUrl = $paymentRequest->callback_url;
        if (!$callbackUrl) {
            return;
        }

        // Dispatch as a job for async processing
        dispatch(function () use ($paymentRequest, $callbackUrl) {
            try {
                Http::timeout(10)->post($callbackUrl, [
                    'payment_id' => $paymentRequest->uuid,
                    'status' => $paymentRequest->status,
                    'amount' => $paymentRequest->amount,
                    'payer_wallet_id' => $paymentRequest->payer_wallet_id,
                    'transaction_reference' => $paymentRequest->transaction?->reference,
                    'paid_at' => now()->toISOString(),
                ]);
            } catch (\Exception $e) {
                Log::error('Webhook failed for payment ' . $paymentRequest->uuid . ': ' . $e->getMessage());
            }
        })->onQueue('webhooks');
    }
}
