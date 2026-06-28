<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('proxy_keys', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            // Key (shifrlangan saqlanadi)
            $table->string('name')->default('Default Key');
            $table->string('key_prefix', 16); // cap-xxxx... (preview)
            $table->text('key_encrypted'); // to'liq kalit shifrlangan
            $table->string('key_hash', 64)->unique(); // SHA256 — qidirish uchun
            // Balans
            $table->decimal('balance_uzs', 14, 2)->default(0);
            $table->decimal('spent_uzs', 14, 2)->default(0);
            // Limitlar
            $table->integer('rate_limit_per_minute')->default(60);
            $table->integer('rate_limit_per_day')->nullable();
            $table->json('allowed_models')->nullable(); // null = hammasi
            $table->json('allowed_ips')->nullable();
            // Statistika
            $table->bigInteger('total_requests')->default(0);
            $table->bigInteger('total_tokens_in')->default(0);
            $table->bigInteger('total_tokens_out')->default(0);
            // Status
            $table->enum('status', ['active', 'paused', 'revoked'])->default('active');
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('user_id');
            $table->index('key_hash');
            $table->index('status');
        });
    }

    public function down(): void {
        Schema::dropIfExists('proxy_keys');
    }
};