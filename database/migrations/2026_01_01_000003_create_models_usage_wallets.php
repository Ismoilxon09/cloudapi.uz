<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        // AI Modellar katalogi
        Schema::create('ai_models', function (Blueprint $table) {
            $table->id();
            $table->string('model_id')->unique(); // openai/gpt-4o
            $table->string('display_name'); // GPT-4o
            $table->string('provider')->default('openrouter');
            $table->string('category')->default('chat'); // chat, reasoning, embedding, image, audio
            $table->text('description')->nullable();
            // Narxlar (1M token uchun, USD)
            $table->decimal('cost_input_usd', 10, 6)->default(0);
            $table->decimal('cost_output_usd', 10, 6)->default(0);
            $table->decimal('margin_percent', 5, 2)->default(30);
            $table->decimal('usd_to_uzs', 10, 2)->default(12700);
            // Sozlamalar
            $table->integer('context_length')->nullable();
            $table->json('capabilities')->nullable(); // ["vision", "tools", "json_mode"]
            $table->boolean('is_free')->default(false);
            $table->boolean('is_featured')->default(false);
            $table->boolean('active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index('active');
            $table->index('category');
            $table->index('provider');
        });

        // Usage log (har so'rov)
        Schema::create('proxy_usage', function (Blueprint $table) {
            $table->id();
            $table->foreignId('proxy_key_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained();
            $table->string('model');
            $table->string('provider')->default('openrouter');
            $table->integer('tokens_in')->default(0);
            $table->integer('tokens_out')->default(0);
            $table->decimal('cost_usd', 12, 8)->default(0);
            $table->decimal('cost_uzs', 12, 4)->default(0);
            $table->integer('latency_ms')->default(0);
            $table->integer('status_code')->default(200);
            $table->text('error')->nullable();
            $table->string('ip', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamp('created_at');

            $table->index(['user_id', 'created_at']);
            $table->index(['proxy_key_id', 'created_at']);
            $table->index('model');
        });

        // Hamyon (asosiy balans)
        Schema::create('wallets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->decimal('balance_uzs', 14, 2)->default(0);
            $table->decimal('total_deposited', 14, 2)->default(0);
            $table->decimal('total_spent', 14, 2)->default(0);
            $table->timestamps();
        });

        // Tranzaksiyalar
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('wallet_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['deposit', 'withdrawal', 'transfer', 'refund', 'bonus', 'usage']);
            $table->enum('status', ['pending', 'completed', 'failed', 'cancelled'])->default('pending');
            $table->decimal('amount_uzs', 14, 2);
            $table->decimal('balance_after', 14, 2)->default(0);
            $table->string('payment_method')->nullable(); // payme, click, card, manual
            $table->string('reference')->nullable(); // tashqi to'lov ID
            $table->json('meta')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
            $table->index('status');
        });
    }

    public function down(): void {
        Schema::dropIfExists('transactions');
        Schema::dropIfExists('wallets');
        Schema::dropIfExists('proxy_usage');
        Schema::dropIfExists('ai_models');
    }
};