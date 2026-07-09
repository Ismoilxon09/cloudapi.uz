<?php

namespace App\Http\Controllers\Bot\Commands;

use App\Models\Feedback;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

/**
 * Bot orqali feedback yozish
 * 
 * Foydalanuvchi /feedback bosadi → yulduz baho tanlaydi → matn yozadi
 */
class FeedbackCommand
{
    protected string $botToken;
    protected string $adminChatId;

    public function __construct()
    {
        $this->botToken = env('TELEGRAM_BOT_TOKEN');
        $this->adminChatId = env('TELEGRAM_ADMIN_CHAT_ID');
    }

    /**
     * /feedback komandasi — yulduz baho tanlash
     */
    public function start(int $chatId, int $telegramId): void
    {
        // State'ni saqlaymiz
        Cache::put("bot_state:{$telegramId}", 'awaiting_feedback_rating', 3600);

        $keyboard = [
            [
                ['text' => '⭐', 'callback_data' => 'fb_rating:1'],
                ['text' => '⭐⭐', 'callback_data' => 'fb_rating:2'],
                ['text' => '⭐⭐⭐', 'callback_data' => 'fb_rating:3'],
            ],
            [
                ['text' => '⭐⭐⭐⭐', 'callback_data' => 'fb_rating:4'],
                ['text' => '⭐⭐⭐⭐⭐', 'callback_data' => 'fb_rating:5'],
            ],
            [
                ['text' => '❌ Bekor qilish', 'callback_data' => 'fb_cancel'],
            ],
        ];

        $this->sendMessage($chatId, 
            "💬 <b>Fikr yozish</b>\n\n" .
            "Platformamiz haqidagi taassurotingizni ulashing.\n\n" .
            "Avval bahoni tanlang:",
            ['reply_markup' => json_encode(['inline_keyboard' => $keyboard])]
        );
    }

    /**
     * Yulduz baho tanlangandan keyin — matn kutish
     */
    public function handleRating(int $chatId, int $telegramId, int $rating, int $messageId): void
    {
        // Rating'ni saqlaymiz
        Cache::put("bot_feedback_rating:{$telegramId}", $rating, 3600);
        Cache::put("bot_state:{$telegramId}", 'awaiting_feedback_text', 3600);

        $stars = str_repeat('⭐', $rating);

        $this->editMessage($chatId, $messageId,
            "💬 <b>Fikr yozish</b>\n\n" .
            "Baho: {$stars}\n\n" .
            "✏️ Endi fikringizni yozib yuboring (kamida 5 belgi):"
        );
    }

    /**
     * User matn yozganda saqlaymiz
     */
    public function saveFeedback(int $chatId, int $telegramId, string $text): void
    {
        $rating = (int) Cache::get("bot_feedback_rating:{$telegramId}", 5);
        $text = trim(strip_tags($text));

        if (mb_strlen($text) < 5) {
            $this->sendMessage($chatId, "⚠️ Fikringiz juda qisqa. Kamida 5 belgi bo'lsin.");
            return;
        }

        if (mb_strlen($text) > 1000) {
            $text = mb_substr($text, 0, 1000);
        }

        // User topish
        $user = User::where('telegram_id', $telegramId)->first();

        // Saqlash
        $feedback = Feedback::create([
            'user_id' => $user?->id,
            'telegram_id' => $telegramId,
            'name' => $user?->name ?? 'Foydalanuvchi',
            'rating' => $rating,
            'text' => $text,
            'is_published' => 1,
            'source' => 'telegram',
        ]);

        // State tozalash
        Cache::forget("bot_state:{$telegramId}");
        Cache::forget("bot_feedback_rating:{$telegramId}");

        $stars = str_repeat('⭐', $rating);
        $this->sendMessage($chatId,
            "✅ <b>Rahmat!</b>\n\n" .
            "Sizning fikringiz saqlandi.\n" .
            "Baho: {$stars}\n\n" .
            "Fikringiz bosh sahifada ko'rinadi. Siz bilan uchun rahmat! 💙"
        );

        // Admin'ga notif
        $this->notifyAdmin($feedback, $user);
    }

    /**
     * Bekor qilish
     */
    public function cancel(int $chatId, int $telegramId, int $messageId): void
    {
        Cache::forget("bot_state:{$telegramId}");
        Cache::forget("bot_feedback_rating:{$telegramId}");

        $this->editMessage($chatId, $messageId,
            "❌ Fikr yozish bekor qilindi.\n\n" .
            "Fikr bildirish uchun istalgan vaqtda /feedback yuboring."
        );
    }

    protected function notifyAdmin(Feedback $feedback, ?User $user): void
    {
        if (!$this->adminChatId) return;

        $stars = str_repeat('⭐', $feedback->rating);
        $name = $user?->name ?? 'Anonim';
        $text = htmlspecialchars($feedback->text, ENT_QUOTES);

        $message = "💬 <b>Yangi feedback (bot)</b>\n\n";
        $message .= "<b>Kimdan:</b> " . htmlspecialchars($name, ENT_QUOTES) . "\n";
        $message .= "<b>Baho:</b> {$stars} ({$feedback->rating}/5)\n\n";
        $message .= "<b>Fikr:</b>\n{$text}";

        $this->sendMessage($this->adminChatId, $message);
    }

    protected function sendMessage(int|string $chatId, string $text, array $extra = []): void
    {
        try {
            Http::timeout(5)->post(
                "https://api.telegram.org/bot{$this->botToken}/sendMessage",
                array_merge([
                    'chat_id' => $chatId,
                    'text' => $text,
                    'parse_mode' => 'HTML',
                    'disable_web_page_preview' => true,
                ], $extra)
            );
        } catch (\Exception $e) {
            \Log::warning("Bot send failed: " . $e->getMessage());
        }
    }

    protected function editMessage(int|string $chatId, int $messageId, string $text, array $extra = []): void
    {
        try {
            Http::timeout(5)->post(
                "https://api.telegram.org/bot{$this->botToken}/editMessageText",
                array_merge([
                    'chat_id' => $chatId,
                    'message_id' => $messageId,
                    'text' => $text,
                    'parse_mode' => 'HTML',
                    'disable_web_page_preview' => true,
                ], $extra)
            );
        } catch (\Exception $e) {
            \Log::warning("Bot edit failed: " . $e->getMessage());
        }
    }
}