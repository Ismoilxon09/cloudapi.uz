<?php

namespace App\Console\Commands;

use App\Services\OpenRouter\ModelSyncService;
use Illuminate\Console\Command;

class SyncOpenRouterModels extends Command
{
    protected $signature = 'openrouter:sync';
    protected $description = 'OpenRouter dan barcha modellarni sync qilish';

    public function handle(ModelSyncService $service): int
    {
        $this->info('OpenRouter dan modellar sync qilinmoqda...');

        try {
            $stats = $service->syncAll();

            $this->newLine();
            $this->info('Sync yakunlandi:');
            $this->table(
                ['Status', 'Soni'],
                [
                    ['Yangi qo\'shildi', $stats['created']],
                    ['Yangilandi', $stats['updated']],
                    ['O\'tkazib yuborildi', $stats['skipped']],
                    ['Xatoliklar', $stats['errors']],
                ]
            );

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Xatolik: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}