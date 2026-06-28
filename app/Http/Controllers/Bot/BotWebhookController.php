<?php

namespace App\Http\Controllers\Bot;

use App\Http\Controllers\Bot\Commands\BalanceCommand;
use App\Http\Controllers\Bot\Commands\ContactCommand;
use App\Http\Controllers\Bot\Commands\DailyCommand;
use App\Http\Controllers\Bot\Commands\HelpCommand;
use App\Http\Controllers\Bot\Commands\ReferralCommand;
use App\Http\Controllers\Bot\Commands\StartCommand;
use App\Http\Controllers\Bot\Commands\TasksCommand;
use App\Http\Controllers\Bot\Commands\TopupCommand;
use App\Http\Controllers\Controller;
use App\Services\Telegram\TelegramService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class BotWebhookController extends Controller
{
    public function __construct(protected TelegramService $tg) {}

    /**
     * Webhook qabul qiladi
     */
    public function handle(Request $request, string $secret)
    {
        // Secret token tekshiruvi
        $expectedSecret = env('TELEGRAM_WEBHOOK_SECRET');
        if (!$expectedSecret || $secret !== $expectedSecret) {
            Log::warning('Invalid webhook secret');
            return response('Unauthorized', 401);
        }

        $update = $request->all();

        try {
            // Callback query (inline tugma bosilganda)
            if (isset($update['callback_query'])) {
                $this->handleCallback($update['callback_query']);
                return response('OK');
            }

            // Oddiy xabar
            if (isset($update['message'])) {
                $this->handleMessage($update['message']);
                return response('OK');
            }
        } catch (\Throwable $e) {
            Log::error('Bot webhook error', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
        }

        return response('OK');
    }

    /**
     * Oddiy xabarlarni boshqarish
     */
    protected function handleMessage(array $message): void
    {
        $chatId = $message['chat']['id'] ?? null;
        if (!$chatId) return;

        // Contact share qabul qilish
        if (isset($message['contact'])) {
            (new ContactCommand($this->tg))->handle($message);
            return;
        }

        // Photo (chek yuborildi)
        if (isset($message['photo'])) {
            (new TopupCommand($this->tg))->handlePhoto($message);
            return;
        }

        $text = trim($message['text'] ?? '');
        if (empty($text)) return;

        // Komandalar
        if ($text === '/start' || str_starts_with($text, '/start ')) {
            (new StartCommand($this->tg))->handle($message);
            return;
        }

        // Inline buttonlar (Reply keyboard)
        $cmdMap = [
            '/balance'    => BalanceCommand::class,
            '💰 Balans'   => BalanceCommand::class,
            '/tasks'      => TasksCommand::class,
            '/vazifalar'  => TasksCommand::class,
            '✅ Vazifalar' => TasksCommand::class,
            '/daily'      => DailyCommand::class,
            '🎁 Kunlik bonus' => DailyCommand::class,
            '/topup'      => TopupCommand::class,
            '💳 To\'ldirish' => TopupCommand::class,
            '/referral'   => ReferralCommand::class,
            '👥 Referral' => ReferralCommand::class,
            '/help'       => HelpCommand::class,
            '❓ Yordam'   => HelpCommand::class,
        ];

        if (isset($cmdMap[$text])) {
            $cmdClass = $cmdMap[$text];
            (new $cmdClass($this->tg))->handle($message);
            return;
        }

        // State-based input (masalan, topup miqdori kutilyapti)
        $this->handleStateInput($message);
    }

    /**
     * Callback (inline keyboard) bosilganda
     */
    protected function handleCallback(array $callback): void
    {
        $data = $callback['data'] ?? '';
        $callbackId = $callback['id'];

        // Format: "action:param1:param2"
        $parts = explode(':', $data);
        $action = $parts[0];

        // Tezda tugmani "yuklanyapti" deb belgilash
        $this->tg->answerCallbackQuery($callbackId);

        switch ($action) {
            case 'task_check':
                (new TasksCommand($this->tg))->handleCheck($callback, $parts[1] ?? null);
                break;

            case 'daily_claim':
                (new DailyCommand($this->tg))->handleClaim($callback);
                break;

            case 'topup_amount':
                (new TopupCommand($this->tg))->handleAmount($callback, $parts[1] ?? null);
                break;

            case 'admin_topup_approve':
                (new TopupCommand($this->tg))->adminApprove($callback, $parts[1] ?? null);
                break;

            case 'admin_topup_reject':
                (new TopupCommand($this->tg))->adminReject($callback, $parts[1] ?? null);
                break;

            default:
                Log::info("Unknown callback action: {$action}");
        }
    }

    /**
     * State-based input (masalan, "Topup miqdor yozing")
     */
    protected function handleStateInput(array $message): void
    {
        // Implementatsiya keyin (state management qo'shilganda)
        // Hozircha — har yo'qotilgan xabarga help
        $this->tg->sendMessage($message['chat']['id'],
            "Tushunmadim 🤔\n\nMavjud buyruqlar uchun /help bosing.");
    }
}