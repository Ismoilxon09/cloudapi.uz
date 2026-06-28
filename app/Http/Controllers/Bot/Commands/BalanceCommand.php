<?php

namespace App\Http\Controllers\Bot\Commands;

use App\Models\User;
use App\Services\Telegram\TelegramService;

class BalanceCommand
{
    public function __construct(protected TelegramService $tg) {}

    public function handle(array $message): void
    {
        $chatId = $message['chat']['id'];
        $telegramId = $message['from']['id'];

        $user = User::where('telegram_id', $telegramId)->with('wallet')->first();
        if (!$user || !$user->wallet) {
            $this->tg->sendMessage($chatId, "❌ Hisob topilmadi. /start ni bosing.");
            return;
        }

        $balance = $user->wallet->balance_uzs;
        $bonus = $user->wallet->bonus_balance_uzs;
        $total = $balance + $bonus;

        $text = "💼 <b>Sizning hamyonlaringiz</b>\n\n";
        $text .= "💰 Asosiy hamyon:\n";
        $text .= "    <b>" . number_format($balance, 0, '.', ' ') . " so'm</b>\n\n";
        $text .= "🎁 Bonus hamyon (GP):\n";
        $text .= "    <b>" . number_format($bonus, 0, '.', ' ') . " GP</b>\n";
        $text .= "    <i>(1 GP = 1 so'm, API uchun)</i>\n\n";
        $text .= "━━━━━━━━━━━━━━━\n";
        $text .= "📊 Jami: <b>" . number_format($total, 0, '.', ' ') . " so'm</b>\n\n";

        if ($total < 1000) {
            $text .= "⚠️ Balansingiz juda kam. To'ldiring yoki vazifalar bajaring.";
        }

        $keyboard = [
            [
                ['text' => '💳 To\'ldirish', 'callback_data' => 'menu:topup'],
                ['text' => '✅ Vazifalar', 'callback_data' => 'menu:tasks'],
            ],
            [
                ['text' => '🌐 Web platforma', 'url' => 'https://cloudapi.uz/dashboard'],
            ],
        ];

        $this->tg->sendMessageWithKeyboard($chatId, $text, $keyboard);
    }
}