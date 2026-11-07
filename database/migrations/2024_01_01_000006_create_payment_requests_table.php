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
        Schema::create('payment_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('api_client_id')->constrained('api_clients')->cascadeOnDelete();
            $table->uuid('uuid')->unique();
            $table->decimal('amount', 16, 4);
            $table->text('description')->nullable();
            $table->string('callback_url', 500)->nullable();
            $table->string('status')->default('pending'); // enum: pending, paid, expired, cancelled
            $table->foreignId('payer_wallet_id')->nullable()->constrained('wallets');
            $table->foreignId('transaction_id')->nullable()->constrained('transactions');
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_requests');
    }
};

