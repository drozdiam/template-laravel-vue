<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('bybit_tx_id');
            $table->string('type', 16);
            $table->string('status', 32)->nullable();
            $table->decimal('amount', 20, 8);
            $table->string('currency', 10);
            $table->decimal('fiat_amount', 15, 2)->nullable();
            $table->string('fiat_currency', 3)->nullable();
            $table->string('merchant')->nullable();
            $table->text('description')->nullable();
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamp('occurred_at');
            $table->jsonb('raw_payload');
            $table->unsignedBigInteger('telegram_message_id')->nullable();
            $table->timestamp('categorized_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'bybit_tx_id']);
            $table->index(['user_id', 'occurred_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};