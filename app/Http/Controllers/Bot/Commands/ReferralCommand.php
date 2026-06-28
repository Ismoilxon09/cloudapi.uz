<?php

namespace App\Http\Controllers\Bot\Commands;

use App\Models\Referral;
use App\Models\User;
use App\Services\Telegram\TelegramService;

class ReferralCommand
{
    public function __construct(protected TelegramService $tg) {}

    public function handle(array $message): void
    {
        $chatId = $message['chat']['id'];
        $telegramId = $message['from']['id'];

        $user = User::where('telegram_id', $telegramId)->first();
        if (!$user) {
            $this->tg->sendMessage($chatId, "❌ /start ni bosing.");
            return;
        }

        $botUsername = env('TELEGRAM_BOT_USERNAME', 'cloudapiuzbot');
        $refLink = "https://t.me/{$botUsername}?start=ref_{$user->referral_code}";

        $refCount = Referral::where('referrer_id', $user->id)->count();
        $refEarned = Referral::where('referrer_id', $user->id)->sum('bonus_gp');

        $text = "👥 <b>Referral dasturi</b>\n\n";
        $text .= "🔗 Sizning taklif linkingiz:\n";
        $text .= "<code>{$refLink}</code>\n\n";
        $text .= "📊 <b>Statistikangiz:</b>\n";
        $text .= "    👥 Kelganlar: <b>{$refCount}</b>\n";
        $text .= "    💎 Topganlar: <b>{$refEarned} GP</b>\n\n";
        $text .= "💡 <b>Qanday ishlaydi?</b>\n";
        $text .= "Linkni do'stingizga yuboring. U /start bosganda har ikkalangizga 10 GP beriladi!";

        $keyboard = [
            [
                ['text' => '📤 Linkni ulashish',
                 'url' => "https://t.me/share/url?url=" . urlencode($refLink) .
                         "&text=" . urlencode("🚀 CloudAPI — O'zbekiston AI darvozasi!\n\nMening taklif linkim orqali qo'shiling va bonusga ega bo'ling 🎁")],
            ],
        ];

        $this->tg->sendMessageWithKeyboard($chatId, $text, $keyboard);
    }
}