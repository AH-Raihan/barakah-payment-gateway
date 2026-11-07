<?php

namespace RelicSoftware\BarakahPayment\Responses;

class PaymentStatusResponse
{
    public function __construct(protected array $data) {}

    public function getPaymentId(): string
    {
        return $this->data['payment_id'] ?? '';
    }

    public function getStatus(): string
    {
        return $this->data['status'] ?? 'unknown';
    }

    public function isPaid(): bool
    {
        return $this->getStatus() === 'paid';
    }

    public function isPending(): bool
    {
        return $this->getStatus() === 'pending';
    }

    public function isExpired(): bool
    {
        return $this->getStatus() === 'expired';
    }

    public function getAmount(): float
    {
        return (float) ($this->data['amount'] ?? 0.0);
    }

    public function getDescription(): string
    {
        return $this->data['description'] ?? '';
    }

    public function getTransactionReference(): ?string
    {
        return $this->data['transaction_reference'] ?? null;
    }

    public function getPaidAt(): ?string
    {
        return $this->data['paid_at'] ?? null;
    }

    public function toArray(): array
    {
        return $this->data;
    }
}

