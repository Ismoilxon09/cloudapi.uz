<?php

namespace App\Http\Controllers\Bot\Commands;

use App\Models\User;
use App\Services\Telegram\TelegramService;

class ContactCommand
{
    public function __construct(protected TelegramService $tg) {}

    public function handle(array $message): void
    {
        $chatId = $message['chat']['id'];
        $telegramId = $message['from']['id'];
        $contact = $message['contact'];

        // Faqat o'z telefon raqamini yuborganga ruxsat
        if ((int)$contact['user_id'] !== (int)$telegramId) {
            $this->tg->sendMessage($chatId,
                "❌ Faqat o'z telefon raqamingizni yuborishingiz mumkin.\n\n" .
                "Iltimos qaytadan urinib ko'ring.");
            return;
        }

        // Telefon raqamni tozalash (faqat raqamlar)
        $phone = preg_replace('/[^\d]/', '', $contact['phone_number']);

        $user = User::where('telegram_id', $telegramId)->first();
        if (!$user) {
            $this->tg->sendMessage($chatId,
                "❌ Akkaunt topilmadi. /start ni qayta bosing.");
            return;
        }

        // Saqlash
        $user->update([
            'phone' => $phone,
            'phone_verified_at' => now(),
        ]);

        // Tasdiqlash xabari
        $formattedPhone = $this->formatPhone($phone);

        $this->tg->sendMessage($chatId,
            "✅ <b>Telefon raqamingiz saqlandi!</b>\n\n" .
            "📱 {$formattedPhone}\n\n" .
            "Endi web platformada ham shu raqam orqali kira olasiz.",
            ['reply_markup' => json_encode(['remove_keyboard' => true])]
        );

        // Asosiy menyu
        (new StartCommand($this->tg))->handle([
            'chat' => ['id' => $chatId],
            'from' => $message['from'],
            'text' => '/start',
        ]);
    }

    protected function formatPhone(string $phone): string
    {
        // 998901234567 → +998 90 123 45 67
        if (strlen($phone) === 12 && str_starts_with($phone, '998')) {
            return sprintf('+%s %s %s %s %s',
                substr($phone, 0, 3),
                substr($phone, 3, 2),
                substr($phone, 5, 3),
                substr($phone, 8, 2),
                substr($phone, 10, 2)
            );
        }
        return '+' . $phone;
    }
}