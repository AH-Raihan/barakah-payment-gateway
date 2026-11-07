<?php

namespace App\Services;

use App\Models\Wallet;
use App\Models\Transaction;
use App\Models\TransactionLog;
use App\Events\TransactionCompleted;
use App\Exceptions\InsufficientBalanceException;
use App\Exceptions\DuplicateTransactionException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class WalletService
{
    /**
     * Transfer money between wallets with full safety guarantees.
     * Uses DB transaction + row locking + idempotency.
     */
    public function transfer(
        Wallet $senderWallet,
        Wallet $receiverWallet,
        float $amount,
        string $type = 'send_money',
        ?string $description = null,
        ?string $idempotencyKey = null,
        ?string $ipAddress = null,
        ?string $userAgent = null
    ): Transaction {
        // Validate amount
        if ($amount <= 0) {
            throw new \InvalidArgumentException('Amount must be greater than zero.');
        }

        // Check idempotency - if this key was already used, return existing transaction
        if ($idempotencyKey) {
            $existing = Transaction::where('idempotency_key', $idempotencyKey)->first();
            if ($existing) {
                // Return the existing transaction instead of creating duplicate
                return $existing;
            }
        }

        return DB::transaction(function () use (
            $senderWallet, $receiverWallet, $amount, $type,
            $description, $idempotencyKey, $ipAddress, $userAgent
        ) {
            // Lock both wallets (always lock in ID order to prevent deadlocks)
            $walletIds = collect([$senderWallet->id, $receiverWallet->id])->sort()->values();
            $lockedWallets = Wallet::whereIn('id', $walletIds)->lockForUpdate()->get()->keyBy('id');

            $sender = $lockedWallets[$senderWallet->id];
            $receiver = $lockedWallets[$receiverWallet->id];

            // Check balance AFTER acquiring lock
            if ($sender->balance < $amount) {
                throw new InsufficientBalanceException(
                    'Insufficient balance. Available: ' . number_format($sender->balance, 2) . ' BDT'
                );
            }

            // Check wallet statuses
            if ($sender->status !== 'active') {
                throw new \RuntimeException('Sender wallet is not active.');
            }
            if ($receiver->status !== 'active') {
                throw new \RuntimeException('Receiver wallet is not active.');
            }

            // Debit sender
            $sender->decrement('balance', $amount);

            // Credit receiver
            $receiver->increment('balance', $amount);

            // Create transaction record
            $transaction = Transaction::create([
                'uuid' => Str::uuid()->toString(),
                'sender_wallet_id' => $sender->id,
                'receiver_wallet_id' => $receiver->id,
                'type' => $type,
                'amount' => $amount,
                'fee' => 0, // No fees for institutional transfers
                'description' => $description,
                'reference' => 'TXN' . strtoupper(Str::random(12)),
                'status' => 'completed',
                'idempotency_key' => $idempotencyKey,
                'ip_address' => $ipAddress,
                'user_agent' => $userAgent,
                'completed_at' => now(),
            ]);

            // Log the transaction
            TransactionLog::create([
                'transaction_id' => $transaction->id,
                'action' => 'completed',
                'old_status' => 'pending',
                'new_status' => 'completed',
                'metadata' => [
                    'sender_balance_after' => $sender->fresh()->balance,
                    'receiver_balance_after' => $receiver->fresh()->balance,
                ],
            ]);

            // Fire event
            event(new TransactionCompleted($transaction));

            return $transaction;
        });
    }

    /**
     * Deposit money into a wallet (admin action - no sender wallet needed)
     */
    public function deposit(
        Wallet $wallet,
        float $amount,
        ?string $description = null,
        ?int $adminId = null,
        ?string $ipAddress = null
    ): Transaction {
        if ($amount <= 0) {
            throw new \InvalidArgumentException('Amount must be greater than zero.');
        }

        return DB::transaction(function () use ($wallet, $amount, $description, $adminId, $ipAddress) {
            // Lock wallet
            $wallet = Wallet::where('id', $wallet->id)->lockForUpdate()->first();

            if ($wallet->status !== 'active') {
                throw new \RuntimeException('Wallet is not active.');
            }

            // Credit wallet
            $wallet->increment('balance', $amount);

            // Create transaction
            $transaction = Transaction::create([
                'uuid' => Str::uuid()->toString(),
                'sender_wallet_id' => null,
                'receiver_wallet_id' => $wallet->id,
                'type' => 'deposit',
                'amount' => $amount,
                'fee' => 0,
                'description' => $description ?? 'Admin deposit',
                'reference' => 'DEP' . strtoupper(Str::random(12)),
                'status' => 'completed',
                'ip_address' => $ipAddress,
                'completed_at' => now(),
            ]);

            // Log
            TransactionLog::create([
                'transaction_id' => $transaction->id,
                'action' => 'deposit_completed',
                'old_status' => null,
                'new_status' => 'completed',
                'performed_by' => $adminId,
                'metadata' => [
                    'wallet_balance_after' => $wallet->fresh()->balance,
                ],
            ]);

            // Record admin deposit
            if ($adminId) {
                \App\Models\AdminDeposit::create([
                    'admin_id' => $adminId,
                    'wallet_id' => $wallet->id,
                    'amount' => $amount,
                    'reference' => $transaction->reference,
                    'note' => $description,
                    'transaction_id' => $transaction->id,
                ]);
            }

            // Fire event
            event(new TransactionCompleted($transaction));

            return $transaction;
        });
    }

    /**
     * Cash out from wallet (like ATM withdrawal - user to agent)
     */
    public function cashOut(
        Wallet $userWallet,
        Wallet $agentWallet,
        float $amount,
        ?string $description = null,
        ?string $idempotencyKey = null,
        ?string $ipAddress = null,
        ?string $userAgent = null
    ): Transaction {
        return $this->transfer(
            $userWallet,
            $agentWallet,
            $amount,
            'cash_out',
            $description ?? 'Cash out',
            $idempotencyKey,
            $ipAddress,
            $userAgent
        );
    }

    /**
     * Cash in (agent deposits cash for user)
     */
    public function cashIn(
        Wallet $agentWallet,
        Wallet $userWallet,
        float $amount,
        ?string $description = null,
        ?string $idempotencyKey = null,
        ?string $ipAddress = null,
        ?string $userAgent = null
    ): Transaction {
        return $this->transfer(
            $agentWallet,
            $userWallet,
            $amount,
            'cash_in',
            $description ?? 'Cash in',
            $idempotencyKey,
            $ipAddress,
            $userAgent
        );
    }
}
