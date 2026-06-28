<?php

namespace App\Console\Commands;

use App\Http\Controllers\Bot\BotWebhookController;
use App\Services\Telegram\TelegramService;
use Illuminate\Console\Command;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class TelegramPollCommand extends Command
{
    protected $signature = 'tg:poll';
    protected $description = 'Bot uchun long polling (local test)';

    public function handle(TelegramService $tg, BotWebhookController $ctrl): int
    {
        if (!$tg->isConfigured()) {
            $this->error('TELEGRAM_BOT_TOKEN yo\'q!');
            return 1;
        }

        // Webhook'ni o'chirib qo'yish (polling bilan to'qnashmasligi uchun)
        $this->info('Webhook o\'chirilmoqda (polling uchun)...');
        $tg->deleteWebhook();

        $token = env('TELEGRAM_BOT_TOKEN');
        $offset = 0;

        $this->info('🤖 Bot polling boshlandi. To\'xtatish: Ctrl+C');
        $this->newLine();

        while (true) {
            try {
                $response = Http::timeout(35)->get("https://api.telegram.org/bot{$token}/getUpdates", [
                    'offset' => $offset,
                    'timeout' => 30,
                    'allowed_updates' => ['message', 'callback_query'],
                ]);

                $data = $response->json();

                if (!($data['ok'] ?? false)) {
                    $this->error('API xato: ' . json_encode($data));
                    sleep(5);
                    continue;
                }

                $updates = $data['result'] ?? [];

                foreach ($updates as $update) {
                    $offset = $update['update_id'] + 1;

                    $this->line("📨 Update #{$update['update_id']}");

                    // Webhook controller'ni chaqirish
                    $request = Request::create('/api/bot/webhook/dummy', 'POST', $update);
                    try {
                        $ctrl->handle($request, env('TELEGRAM_WEBHOOK_SECRET'));
                    } catch (\Throwable $e) {
                        $this->error('Xato: ' . $e->getMessage());
                    }
                }

                if (empty($updates)) {
                    // Belgini ko'rsatish
                    echo '.';
                }

            } catch (\Throwable $e) {
                $this->error('Polling xato: ' . $e->getMessage());
                sleep(3);
            }
        }

        return 0;
    }
}