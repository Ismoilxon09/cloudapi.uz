<?php

namespace App\Http\Controllers\Bot\Commands;

use App\Services\Telegram\TelegramService;

class HelpCommand
{
    public function __construct(protected TelegramService $tg) {}

    public function handle(array $message): void
    {
        $chatId = $message['chat']['id'];

        $text = "❓ <b>CloudAPI bot yordamchi</b>\n\n";
        $text .= "📌 <b>Mavjud buyruqlar:</b>\n\n";
        $text .= "💰 /balance — Balans ko'rish\n";
        $text .= "✅ /vazifalar — Vazifalar ro'yxati\n";
        $text .= "🎁 /daily — Kunlik bonus\n";
        $text .= "💳 /topup — Hisob to'ldirish\n";
        $text .= "👥 /referral — Referral linki\n";
        $text .= "❓ /help — Bu yordam\n\n";
        $text .= "🌐 <b>Asosiy ish web platformada:</b>\n";
        $text .= "https://cloudapi.uz\n\n";
        $text .= "📞 <b>Aloqa:</b>\n";
        $text .= "Savol/muammo: @coder_nurmatov\n";
        $text .= "📢 Yangiliklar: @cloudapinews";

        $this->tg->sendMessage($chatId, $text);
    }
}