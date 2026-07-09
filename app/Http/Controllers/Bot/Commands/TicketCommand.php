<?php

namespace App\Http\Controllers\Bot\Commands;

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

/**
 * Bot orqali ticket ochish
 * 
 * /ticket → Subject kutish → Message kutish → Saqlash
 */
class TicketCommand
{
    protected string $botToken;
    protected string $adminChatId;

    public function __construct()
    {
        $this->botToken = env('TELEGRAM_BOT_TOKEN');
        $this->adminChatId = env('TELEGRAM_ADMIN_CHAT_ID');
    }

    /**
     * /ticket komandasi — subject kutish
     */
    public function start(int $chatId, int $telegramId): void
    {
        Cache::put("bot_state:{$telegramId}", 'awaiting_ticket_subject', 3600);

        $this->sendMessage($chatId,
            "🎫 <b>Yangi ticket</b>\n\n" .
            "Muammo yoki savolingizni bizga yuboring. Jamoamiz javob beradi.\n\n" .
            "📝 <b>1-qadam:</b> Sarlavha yozing (masalan: \"API ishlamayapti\", \"To'lov muammosi\")\n\n" .
            "❌ Bekor qilish uchun: /cancel"
        );
    }

    /**
     * Subject qabul qilingandan keyin message kutish
     */
    public function handleSubject(int $chatId, int $telegramId, string $subject): void
    {
        $subject = trim(strip_tags($subject));

        if (mb_strlen($subject) < 5) {
            $this->sendMessage($chatId, "⚠️ Sarlavha juda qisqa. Kamida 5 belgi bo'lsin. Qayta yozing:");
            return;
        }

        if (mb_strlen($subject) > 200) {
            $subject = mb_substr($subject, 0, 200);
        }

        Cache::put("bot_ticket_subject:{$telegramId}", $subject, 3600);
        Cache::put("bot_state:{$telegramId}", 'awaiting_ticket_message', 3600);

        $this->sendMessage($chatId,
            "✅ Sarlavha saqlandi:\n<i>{$subject}</i>\n\n" .
            "📝 <b>2-qadam:</b> Endi muammoni batafsil tushuntiring (kamida 10 belgi):\n\n" .
            "❌ Bekor qilish: /cancel"
        );
    }

    /**
     * Message qabul qilingandan keyin saqlash
     */
    public function handleMessage(int $chatId, int $telegramId, string $message): void
    {
        $message = trim(strip_tags($message));

        if (mb_strlen($message) < 10) {
            $this->sendMessage($chatId, "⚠️ Xabar juda qisqa. Kamida 10 belgi bo'lsin. Qayta yozing:");
            return;
        }

        if (mb_strlen($message) > 2000) {
            $message = mb_substr($message, 0, 2000);
        }

        $subject = Cache::get("bot_ticket_subject:{$telegramId}", 'Ticket');
        $user = User::where('telegram_id', $telegramId)->first();

        $ticket = Ticket::create([
            'user_id' => $user?->id,
            'telegram_id' => $telegramId,
            'subject' => $subject,
            'message' => $message,
            'status' => 'open',
            'priority' => 'normal',
            'source' => 'telegram',
        ]);

        // State tozalash
        Cache::forget("bot_state:{$telegramId}");
        Cache::forget("bot_ticket_subject:{$telegramId}");

        $this->sendMessage($chatId,
            "✅ <b>Ticket muvaffaqiyatli ochildi!</b>\n\n" .
            "🎫 <b>ID:</b> #{$ticket->id}\n" .
            "📝 <b>Sarlavha:</b> {$subject}\n" .
            "📌 <b>Holat:</b> Ochiq\n\n" .
            "Tez orada javob olasiz.\n\n" .
            "Ticketlarni ko'rish: /my_tickets"
        );

        // Admin'ga notif
        $this->notifyAdmin($ticket, $user);
    }

    /**
     * /my_tickets — user ticketlar ro'yxati
     */
    public function myTickets(int $chatId, int $telegramId): void
    {
        $tickets = Ticket::where('telegram_id', $telegramId)
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        if ($tickets->isEmpty()) {
            $this->sendMessage($chatId,
                "📋 Sizda hali ticket yo'q.\n\n" .
                "Yangi ticket ochish uchun: /ticket"
            );
            return;
        }

        $text = "📋 <b>Sizning ticketlaringiz:</b>\n\n";
        foreach ($tickets as $t) {
            $emoji = match ($t->status) {
                'open' => '🟡',
                'answered' => '🟢',
                'in_progress' => '🔵',
                'closed' => '⚫',
                default => '⚪',
            };
            $subject = htmlspecialchars(mb_substr($t->subject, 0, 50), ENT_QUOTES);
            $text .= "{$emoji} <b>#{$t->id}</b> {$subject}\n";
            $text .= "   <i>{$t->status_label} · {$t->created_at->diffForHumans()}</i>\n\n";
        }

        $text .= "🌐 Batafsil: https://cloudapi.uz/dashboard/tickets";

        $this->sendMessage($chatId, $text);
    }

    /**
     * /cancel — jarayonni bekor qilish
     */
    public function cancel(int $chatId, int $telegramId): void
    {
        Cache::forget("bot_state:{$telegramId}");
        Cache::forget("bot_ticket_subject:{$telegramId}");

        $this->sendMessage($chatId, "❌ Bekor qilindi.");
    }

    protected function notifyAdmin(Ticket $ticket, ?User $user): void
    {
        if (!$this->adminChatId) return;

        $userName = $user?->name ?? 'Anonim';
        $subject = htmlspecialchars($ticket->subject, ENT_QUOTES);
        $message = htmlspecialchars(mb_substr($ticket->message, 0, 300), ENT_QUOTES);

        $text = "🎫 <b>Yangi ticket #{$ticket->id}</b>\n\n";
        $text .= "<b>Kimdan:</b> " . htmlspecialchars($userName, ENT_QUOTES) . "\n";
        $text .= "<b>Manba:</b> Telegram Bot\n\n";
        $text .= "<b>Sarlavha:</b>\n{$subject}\n\n";
        $text .= "<b>Xabar:</b>\n{$message}";

        $keyboard = [[
            ['text' => '💬 Javob berish', 'callback_data' => "ticket_reply:{$ticket->id}"],
            ['text' => '🔒 Yopish', 'callback_data' => "ticket_close:{$ticket->id}"],
        ]];

        $this->sendMessage($this->adminChatId, $text, [
            'reply_markup' => json_encode(['inline_keyboard' => $keyboard])
        ]);
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
}