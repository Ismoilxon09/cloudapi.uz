<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tickets jadvalini yaratadi (agar mavjud bo'lmasa).
 * `migrate` ishonchsiz bo'lgan muhitlar uchun (migrations jadvali bo'sh bo'lsa).
 *
 *   php artisan tickets:setup
 */
class SetupTicketsTable extends Command
{
    protected $signature = 'tickets:setup';
    protected $description = "Support tickets jadvalini yaratadi (agar mavjud bo'lmasa)";

    public function handle(): int
    {
        if (Schema::hasTable('tickets')) {
            $this->info("✓ 'tickets' jadvali allaqachon mavjud — o'zgarish shart emas.");
            return self::SUCCESS;
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

        $this->info("✓ 'tickets' jadvali yaratildi.");
        return self::SUCCESS;
    }
}
