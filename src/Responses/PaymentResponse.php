<?php

namespace RelicSoftware\BarakahPayment\Responses;

use Illuminate\Http\RedirectResponse;

class PaymentResponse
{
    public function __construct(protected array $data) {}

    public function getPaymentId(): string
    {
        return $this->data['payment_id'] ?? '';
    }

    public function getCheckoutUrl(): string
    {
        return $this->data['checkout_url'] ?? '';
    }

    public function getAmount(): float
    {
        return (float) ($this->data['amount'] ?? 0.0);
    }

    public function getCurrency(): string
    {
        return $this->data['currency'] ?? 'BDT';
    }

    public function getStatus(): string
    {
        return $this->data['status'] ?? 'pending';
    }

    public function getExpiresAt(): ?string
    {
        return $this->data['expires_at'] ?? null;
    }

    public function toArray(): array
    {
        return $this->data;
    }

    /**
     * Instantly redirect the browser to the Barakah secure checkout web page.
     */
    public function redirect(): RedirectResponse
    {
        return redirect()->away($this->getCheckoutUrl());
    }
}

