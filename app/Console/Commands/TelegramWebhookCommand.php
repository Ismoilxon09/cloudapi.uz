<?php

namespace App\Console\Commands;

use App\Services\Telegram\TelegramService;
use Illuminate\Console\Command;

class TelegramWebhookCommand extends Command
{
    protected $signature = 'tg:webhook {action=set : set|delete|info}';
    protected $description = 'Telegram webhookni boshqarish';

    public function handle(TelegramService $tg): int
    {
        if (!$tg->isConfigured()) {
            $this->error('TELEGRAM_BOT_TOKEN .env da yo\'q!');
            return 1;
        }

        $action = $this->argument('action');

        switch ($action) {
            case 'set':
                return $this->setWebhook($tg);
            case 'delete':
                return $this->deleteWebhook($tg);
            case 'info':
                return $this->info_($tg);
            default:
                $this->error("Noma'lum action: {$action}");
                return 1;
        }
    }

    protected function setWebhook(TelegramService $tg): int
    {
        $appUrl = config('app.url');
        $secret = env('TELEGRAM_WEBHOOK_SECRET');

        if (!$secret) {
            $this->error('TELEGRAM_WEBHOOK_SECRET .env da yo\'q!');
            return 1;
        }

        if (str_contains($appUrl, 'localhost') || str_contains($appUrl, '127.0.0.1')) {
            $this->error('Webhook URL public bo\'lishi kerak (https://...). Localhost ishlamaydi.');
            $this->warn('Local test uchun ngrok ishlating: https://ngrok.com');
            return 1;
        }

        $url = rtrim($appUrl, '/') . "/api/bot/webhook/{$secret}";

        $this->info("Webhook o'rnatilmoqda: {$url}");
        $result = $tg->setWebhook($url);

        if ($result && ($result['ok'] ?? false)) {
            $this->info('✅ Muvaffaqiyatli!');
            return 0;
        }

        $this->error('❌ Xato: ' . json_encode($result, JSON_UNESCAPED_UNICODE));
        return 1;
    }

    protected function deleteWebhook(TelegramService $tg): int
    {
        $this->info('Webhook o\'chirilmoqda...');
        $result = $tg->deleteWebhook();
        if ($result && ($result['ok'] ?? false)) {
            $this->info('✅ Webhook o\'chirildi');
            return 0;
        }
        $this->error('Xato');
        return 1;
    }

    protected function info_(TelegramService $tg): int
    {
        $info = $tg->getWebhookInfo();
        $me = $tg->getMe();

        $this->info('=== BOT ===');
        if ($me && $me['ok']) {
            $b = $me['result'];
            $this->line("Username: @{$b['username']}");
            $this->line("Nom: {$b['first_name']}");
            $this->line("ID: {$b['id']}");
        }

        $this->newLine();
        $this->info('=== WEBHOOK ===');
        if ($info && $info['ok']) {
            $w = $info['result'];
            $this->line("URL: " . ($w['url'] ?: '(o\'rnatilmagan)'));
            $this->line("Pending: " . ($w['pending_update_count'] ?? 0));
            if (!empty($w['last_error_message'])) {
                $this->error("Oxirgi xato: " . $w['last_error_message']);
            }
        }
        return 0;
    }
}