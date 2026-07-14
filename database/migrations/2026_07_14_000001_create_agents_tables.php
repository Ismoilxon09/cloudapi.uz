<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // AI Agentlar — foydalanuvchi yaratgan agentlar
        Schema::create('agents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            // Identifikatsiya
            $table->string('name');
            $table->string('slug')->unique();          // API / widget uchun
            $table->string('avatar')->nullable();
            $table->text('description')->nullable();

            // Aql / xatti-harakat
            $table->text('system_prompt')->nullable();
            $table->string('behavior_preset')->nullable(); // general|coder|support|sales|custom
            $table->text('greeting')->nullable();           // /start salomi

            // Model konfiguratsiyasi
            $table->enum('model_mode', ['single', 'pool', 'any'])->default('single');
            $table->string('model_slug')->nullable();       // asosiy model (single)
            $table->json('model_pool')->nullable();         // ['gpt-4o-mini', ...] (pool)
            $table->decimal('temperature', 3, 2)->default(0.70);
            $table->integer('max_tokens')->nullable();
            $table->integer('memory_limit')->default(20);   // nechta oldingi xabar kontekstga

            // Holat
            $table->enum('status', ['draft', 'active', 'paused'])->default('draft');
            $table->boolean('is_public')->default(false);

            // Billing / abuse himoyasi (egasi hamyonidan yechiladi)
            $table->decimal('spend_cap_daily_uzs', 14, 2)->nullable(); // null = limitsiz
            $table->decimal('daily_spend_uzs', 14, 2)->default(0);
            $table->date('daily_spend_date')->nullable();
            $table->decimal('total_spent_uzs', 14, 2)->default(0);
            $table->unsignedBigInteger('total_messages')->default(0);
            $table->unsignedBigInteger('total_replies')->default(0);
            $table->timestamp('last_active_at')->nullable();

            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index('status');
        });

        // Kanallar — agent qanday joylarga ulangan (telegram, web, api, discord)
        Schema::create('agent_channels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agent_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['telegram', 'web', 'api', 'discord'])->default('telegram');
            $table->enum('status', ['active', 'inactive'])->default('inactive');
            $table->string('external_id')->nullable();       // tg bot id / username
            $table->string('webhook_secret')->nullable()->unique(); // webhook routing
            $table->json('config')->nullable();              // token (encrypted), origin'lar, ...
            $table->timestamp('connected_at')->nullable();
            $table->timestamps();

            $table->index(['agent_id', 'type']);
        });

        // Suhbatlar — har kanal/chat uchun alohida xotira
        Schema::create('agent_conversations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agent_id')->constrained()->cascadeOnDelete();
            $table->foreignId('channel_id')->nullable()->constrained('agent_channels')->nullOnDelete();
            $table->string('channel_type')->default('telegram');
            $table->string('external_chat_id');              // tg chat id / widget session
            $table->string('external_user_id')->nullable();  // tg from.id
            $table->string('title')->nullable();
            $table->json('meta')->nullable();                // ism, username, ...
            $table->unsignedBigInteger('total_messages')->default(0);
            $table->timestamp('last_message_at')->nullable();
            $table->timestamps();

            $table->unique(['agent_id', 'channel_type', 'external_chat_id'], 'agent_conv_unique');
            $table->index('agent_id');
        });

        // Xabarlar — suhbat tarixi (billing/observability uchun ham)
        Schema::create('agent_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversation_id')->constrained('agent_conversations')->cascadeOnDelete();
            $table->foreignId('agent_id')->constrained()->cascadeOnDelete();
            $table->enum('role', ['user', 'assistant', 'system'])->default('user');
            $table->mediumText('content')->nullable();
            $table->string('model_id')->nullable();
            $table->unsignedInteger('tokens_input')->default(0);
            $table->unsignedInteger('tokens_output')->default(0);
            $table->decimal('cost_uzs', 14, 4)->default(0);
            $table->unsignedInteger('latency_ms')->default(0);
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index(['conversation_id', 'created_at']);
            $table->index(['agent_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agent_messages');
        Schema::dropIfExists('agent_conversations');
        Schema::dropIfExists('agent_channels');
        Schema::dropIfExists('agents');
    }
};
