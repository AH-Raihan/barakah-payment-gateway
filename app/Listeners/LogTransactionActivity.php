<?php

namespace App\Listeners;

use App\Events\TransactionCompleted;
use App\Services\ActivityLogService;
use App\Services\SmsService;

class LogTransactionActivity
{
    /**
     * Create the event listener.
     */
    public function __construct(protected SmsService $smsService)
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(TransactionCompleted $event): void
    {
        $transaction = $event->transaction;

        // Determine the user who initiated the transaction
        $userId = $transaction->senderWallet?->user_id;

        ActivityLogService::log(
            userId: $userId,
            action: 'transaction.' . $transaction->type,
            description: sprintf(
                '%s transaction of %s BDT completed. Reference: %s',
                ucfirst(str_replace('_', ' ', $transaction->type)),
                number_format($transaction->amount, 2),
                $transaction->reference
            ),
            metadata: [
                'transaction_id' => $transaction->id,
                'transaction_uuid' => $transaction->uuid,
                'type' => $transaction->type,
                'amount' => $transaction->amount,
                'reference' => $transaction->reference,
                'sender_wallet_id' => $transaction->sender_wallet_id,
                'receiver_wallet_id' => $transaction->receiver_wallet_id,
                'status' => $transaction->status,
            ]
        );

        // Optional SMS alert to receiver
        $receiverUser = $transaction->receiverWallet?->user;
        if ($receiverUser && !empty($receiverUser->phone)) {
            $smsMsg = sprintf(
                "Barakah: Received %s BDT (Ref: %s). New balance: %s BDT. 0 fee applied.",
                number_format($transaction->amount, 2),
                $transaction->reference,
                number_format($transaction->receiverWallet->balance, 2)
            );
            $this->smsService->send($receiverUser->phone, $smsMsg);
        }
    }
}
