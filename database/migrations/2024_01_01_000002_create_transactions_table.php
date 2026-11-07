<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('sender_wallet_id')->nullable()->constrained('wallets');
            $table->foreignId('receiver_wallet_id')->nullable()->constrained('wallets');
            $table->string('type'); // enum: send_money, cash_out, cash_in, payment, refund, deposit
            $table->decimal('amount', 16, 4);
            $table->decimal('fee', 16, 4)->default(0);
            $table->text('description')->nullable();
            $table->string('reference', 100)->nullable();
            $table->string('status')->default('pending'); // enum: pending, completed, failed, reversed
            $table->string('idempotency_key', 64)->unique()->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index('sender_wallet_id');
            $table->index('receiver_wallet_id');
            $table->index('type');
            $table->index('status');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};

