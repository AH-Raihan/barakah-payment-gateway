<?php

namespace App\Listeners;

use App\Events\TransactionCompleted;
use App\Services\FcmService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendTransactionPushNotification implements ShouldQueue
{
    use InteractsWithQueue;

    public function __construct(protected FcmService $fcmService) {}

    public function handle(TransactionCompleted $event): void
    {
        $tx = $event->transaction->loadMissing(['senderWallet.user', 'receiverWallet.user']);
        $amountFormatted = number_format($tx->amount, 2);

        if ($tx->type === 'deposit') {
            $user = $tx->receiverWallet?->user;
            if ($user) {
                $this->fcmService->sendToUser(
                    $user,
                    'Institutional Deposit Added',
                    "৳{$amountFormatted} BDT has been added to your Barakah wallet.",
                    [
                        'type' => 'deposit',
                        'transaction_id' => $tx->uuid,
                        'reference' => $tx->reference,
                    ]
                );
            }
            return;
        }

        if ($tx->type === 'cash_out') {
            $user = $tx->senderWallet?->user;
            if ($user) {
                $this->fcmService->sendToUser(
                    $user,
                    'Cash Out Successful',
                    "৳{$amountFormatted} BDT withdrawn from your Barakah wallet.",
                    [
                        'type' => 'cash_out',
                        'transaction_id' => $tx->uuid,
                        'reference' => $tx->reference,
                    ]
                );
            }
            return;
        }

        if ($tx->type === 'payment') {
            $merchant = $tx->receiverWallet?->user;
            if ($merchant) {
                $this->fcmService->sendToUser(
                    $merchant,
                    'Payment Received',
                    "৳{$amountFormatted} BDT received for '{$tx->description}'.",
                    [
                        'type' => 'payment',
                        'transaction_id' => $tx->uuid,
                        'reference' => $tx->reference,
                    ]
                );
            }
            return;
        }

        // Standard send_money / P2P transfer
        $sender = $tx->senderWallet?->user;
        $receiver = $tx->receiverWallet?->user;

        // Notify Receiver
        if ($receiver) {
            $senderName = $sender?->name ?? 'Institutional Member';
            $this->fcmService->sendToUser(
                $receiver,
                'Money Received',
                "৳{$amountFormatted} BDT received from {$senderName}.",
                [
                    'type' => 'send_money',
                    'transaction_id' => $tx->uuid,
                    'reference' => $tx->reference,
                ]
            );
        }

        // Notify Sender
        if ($sender) {
            $receiverName = $receiver?->name ?? 'Institutional Member';
            $this->fcmService->sendToUser(
                $sender,
                'Money Sent',
                "৳{$amountFormatted} BDT sent to {$receiverName}. Ref: {$tx->reference}",
                [
                    'type' => 'send_money',
                    'transaction_id' => $tx->uuid,
                    'reference' => $tx->reference,
                ]
            );
        }
    }
}

