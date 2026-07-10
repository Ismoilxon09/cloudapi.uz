<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tickets jadvalini yaratadi va yetishmayotgan ustunlarni qo'shadi.
 * `migrate` ishonchsiz bo'lgan muhitlar uchun (migrations jadvali bo'sh yoki
 * jadval to'liqsiz yaratilgan bo'lsa).
 *
 *   php artisan tickets:setup
 */
class SetupTicketsTable extends Command
{
    protected $signature = 'tickets:setup';
    protected $description = "Tickets jadvalini yaratadi + yetishmagan ustunlarni qo'shadi";

    public function handle(): int
    {
        if (!Schema::hasTable('tickets')) {
            Schema::create('tickets', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id')->nullable();
                $table->bigInteger('telegram_id')->nullable();
                $table->string('subject')->nullable();
                $table->text('message')->nullable();
                $table->string('status')->default('open');
                $table->string('priority')->default('normal');
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
        } else {
            $this->line("• 'tickets' jadvali mavjud — ustunlar tekshirilmoqda...");
        }

        // Yetishmayotgan ustunlarni qo'shish (to'liqsiz jadvallar uchun)
        $columns = [
            'user_id'     => fn (Blueprint $t) => $t->unsignedBigInteger('user_id')->nullable(),
            'telegram_id' => fn (Blueprint $t) => $t->bigInteger('telegram_id')->nullable(),
            'subject'     => fn (Blueprint $t) => $t->string('subject')->nullable(),
            'message'     => fn (Blueprint $t) => $t->text('message')->nullable(),
            'status'      => fn (Blueprint $t) => $t->string('status')->default('open'),
            'priority'    => fn (Blueprint $t) => $t->string('priority')->default('normal'),
            'admin_reply' => fn (Blueprint $t) => $t->text('admin_reply')->nullable(),
            'admin_id'    => fn (Blueprint $t) => $t->unsignedBigInteger('admin_id')->nullable(),
            'replied_at'  => fn (Blueprint $t) => $t->timestamp('replied_at')->nullable(),
            'closed_at'   => fn (Blueprint $t) => $t->timestamp('closed_at')->nullable(),
            'source'      => fn (Blueprint $t) => $t->string('source', 20)->default('web'),
        ];

        $added = [];
        foreach ($columns as $name => $definition) {
            if (!Schema::hasColumn('tickets', $name)) {
                Schema::table('tickets', function (Blueprint $t) use ($definition) {
                    $definition($t);
                });
                $added[] = $name;
            }
        }

        if ($added) {
            $this->info('✓ Qo\'shilgan ustunlar: ' . implode(', ', $added));
        } else {
            $this->info('✓ Barcha ustunlar joyida — o\'zgarish shart emas.');
        }

        return self::SUCCESS;
    }
}
