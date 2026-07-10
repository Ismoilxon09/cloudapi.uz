<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tickets jadvali — support ticketlar uchun.
 * Jadval ba'zi muhitlarda qo'lda yaratilgan bo'lishi mumkin, shuning uchun
 * hasTable() tekshiruvi bilan xavfsiz (mavjud bo'lsa o'tkazib yuboradi).
 */
return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('tickets')) {
            return;
        }

        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->bigInteger('telegram_id')->nullable();
            $table->string('subject');
            $table->text('message');
            $table->enum('status', ['open', 'answered', 'closed', 'in_progress'])->default('open');
            $table->enum('priority', ['low', 'normal', 'high', 'urgent'])->default('normal');
            $table->text('admin_reply')->nullable();
            $table->unsignedBigInteger('admin_id')->nullable();
            $table->timestamp('replied_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->string('source', 20)->default('web');
            $table->timestamps();

            $table->index('user_id');
            $table->index('telegram_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
