<?php

namespace App\Http\Controllers\Bot\Commands;

use App\Models\Feedback;
use App\Models\Ticket;
use App\Models\User;
use App\Models\Transaction;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

/**
 * Bot Admin Panel
 * 
 * /admin — asosiy menyu
 * Faqat TELEGRAM_ADMIN_CHAT_ID egasi kirishi mumkin
 */
class AdminPanelCommand
{
    protected string $botToken;
    protected string $adminChatId;

    public function __construct()
    {
        $this->botToken = env('TELEGRAM_BOT_TOKEN');
        $this->adminChatId = env('TELEGRAM_ADMIN_CHAT_ID');
    }

    /**
     * Admin ekanligini tekshirish
     */
    protected function isAdmin(int $telegramId): bool
    {
        return (string)$telegramId === (string)$this->adminChatId;
    }

    /**
     * /admin — asosiy panel
     */
    public function menu(int $chatId, int $telegramId, ?int $messageId = null): void
    {
        if (!$this->isAdmin($telegramId)) {
            $this->sendMessage($chatId, "⛔ Bu buyruq faqat admin uchun.");
            return;
        }

        $stats = $this->getStats();

        $text = "🛠 <b>Admin Panel</b>\n\n";
        $text .= "📊 <b>Bugungi statistika:</b>\n";
        $text .= "• Yangi user: <b>{$stats['new_users_today']}</b>\n";
        $text .= "• Yangi ticket: <b>{$stats['new_tickets_today']}</b>\n";
        $text .= "• Yangi to'lov: <b>{$stats['new_payments_today']}</b>\n";
        $text .= "• Daromad: <b>" . number_format($stats['revenue_today']) . " UZS</b>\n\n";
        $text .= "📈 <b>Umumiy:</b>\n";
        $text .= "• Jami user: <b>{$stats['total_users']}</b>\n";
        $text .= "• Ochiq ticket: <b>{$stats['open_tickets']}</b>\n";
        $text .= "• Ushbu oy daromad: <b>" . number_format($stats['revenue_month']) . " UZS</b>\n";

        $keyboard = [
            [
                ['text' => '🎫 Ticketlar', 'callback_data' => 'admin_tickets'],
                ['text' => '💬 Feedbacks', 'callback_data' => 'admin_feedbacks'],
            ],
            [
                ['text' => '👥 Foydalanuvchilar', 'callback_data' => 'admin_users'],
                ['text' => '💰 To\'lovlar', 'callback_data' => 'admin_payments'],
            ],
            [
                ['text' => '📊 Statistika', 'callback_data' => 'admin_stats'],
                ['text' => '📢 Broadcast', 'callback_data' => 'admin_broadcast'],
            ],
            [
                ['text' => '⚙️ Sozlamalar', 'callback_data' => 'admin_settings'],
                ['text' => '🚨 Shubhali', 'callback_data' => 'admin_suspicious'],
            ],
            [
                ['text' => '🔄 Yangilash', 'callback_data' => 'admin_menu'],
            ],
        ];

        if ($messageId) {
            $this->editMessage($chatId, $messageId, $text, [
                'reply_markup' => json_encode(['inline_keyboard' => $keyboard])
            ]);
        } else {
            $this->sendMessage($chatId, $text, [
                'reply_markup' => json_encode(['inline_keyboard' => $keyboard])
            ]);
        }
    }

    /**
     * Ochiq ticketlar ro'yxati
     */
    public function tickets(int $chatId, int $telegramId, int $messageId): void
    {
        if (!$this->isAdmin($telegramId)) return;

        $tickets = Ticket::whereIn('status', ['open', 'in_progress'])
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        if ($tickets->isEmpty()) {
            $text = "🎫 <b>Ochiq ticketlar</b>\n\nHozircha ochiq ticket yo'q.";
        } else {
            $text = "🎫 <b>Ochiq ticketlar (" . $tickets->count() . "):</b>\n\n";
            foreach ($tickets as $t) {
                $subject = htmlspecialchars(mb_substr($t->subject, 0, 50), ENT_QUOTES);
                $userName = $t->user?->name ?? 'Anonim';
                $text .= "🎫 <b>#{$t->id}</b> · {$userName}\n";
                $text .= "   {$subject}\n";
                $text .= "   <i>{$t->created_at->diffForHumans()}</i>\n\n";
            }
        }

        $keyboard = [];
        foreach ($tickets as $t) {
            $keyboard[] = [
                ['text' => "📄 #{$t->id} ni ko'rish", 'callback_data' => "admin_ticket:{$t->id}"],
            ];
        }
        $keyboard[] = [['text' => '⬅️ Orqaga', 'callback_data' => 'admin_menu']];

        $this->editMessage($chatId, $messageId, $text, [
            'reply_markup' => json_encode(['inline_keyboard' => $keyboard])
        ]);
    }

    /**
     * Bitta ticket'ni ko'rish
     */
    public function showTicket(int $chatId, int $telegramId, int $messageId, int $ticketId): void
    {
        if (!$this->isAdmin($telegramId)) return;

        $ticket = Ticket::find($ticketId);
        if (!$ticket) {
            $this->answerCallback($chatId, "Ticket topilmadi");
            return;
        }

        $userName = htmlspecialchars($ticket->user?->name ?? 'Anonim', ENT_QUOTES);
        $subject = htmlspecialchars($ticket->subject, ENT_QUOTES);
        $message = htmlspecialchars($ticket->message, ENT_QUOTES);

        $text = "🎫 <b>Ticket #{$ticket->id}</b>\n\n";
        $text .= "<b>Kimdan:</b> {$userName}\n";
        $text .= "<b>Telegram ID:</b> <code>{$ticket->telegram_id}</code>\n";
        $text .= "<b>Holat:</b> {$ticket->status_label}\n";
        $text .= "<b>Sana:</b> {$ticket->created_at->format('d.m.Y H:i')}\n\n";
        $text .= "<b>Sarlavha:</b>\n{$subject}\n\n";
        $text .= "<b>Xabar:</b>\n{$message}";

        if ($ticket->admin_reply) {
            $reply = htmlspecialchars($ticket->admin_reply, ENT_QUOTES);
            $text .= "\n\n<b>✅ Sizning javobingiz:</b>\n{$reply}";
        }

        $keyboard = [];
        if ($ticket->status !== 'closed') {
            $keyboard[] = [
                ['text' => '💬 Javob berish', 'callback_data' => "admin_reply:{$ticket->id}"],
                ['text' => '🔒 Yopish', 'callback_data' => "admin_close:{$ticket->id}"],
            ];
        }
        $keyboard[] = [['text' => '⬅️ Ticketlar', 'callback_data' => 'admin_tickets']];

        $this->editMessage($chatId, $messageId, $text, [
            'reply_markup' => json_encode(['inline_keyboard' => $keyboard])
        ]);
    }

    /**
     * Ticket'ga javob berish rejimi
     */
    public function replyToTicket(int $chatId, int $telegramId, int $ticketId): void
    {
        if (!$this->isAdmin($telegramId)) return;

        Cache::put("admin_replying_ticket:{$telegramId}", $ticketId, 3600);
        Cache::put("bot_state:{$telegramId}", 'admin_ticket_reply', 3600);

        $ticket = Ticket::find($ticketId);
        $subject = htmlspecialchars($ticket->subject, ENT_QUOTES);

        $this->sendMessage($chatId,
            "💬 <b>Javob yozing: #{$ticketId}</b>\n\n" .
            "Sarlavha: <i>{$subject}</i>\n\n" .
            "Endi javobingizni yozib yuboring.\n\n" .
            "❌ Bekor qilish: /cancel"
        );
    }

    /**
     * Admin javob yozib yuborganda saqlash
     */
    public function saveReply(int $chatId, int $telegramId, string $reply): void
    {
        if (!$this->isAdmin($telegramId)) return;

        $ticketId = Cache::get("admin_replying_ticket:{$telegramId}");
        if (!$ticketId) {
            $this->sendMessage($chatId, "Ticket topilmadi.");
            return;
        }

        $ticket = Ticket::find($ticketId);
        if (!$ticket) return;

        $ticket->update([
            'admin_reply' => trim(strip_tags($reply)),
            'admin_id' => auth()->id() ?? null,
            'status' => 'answered',
            'replied_at' => now(),
        ]);

        Cache::forget("admin_replying_ticket:{$telegramId}");
        Cache::forget("bot_state:{$telegramId}");

        // User'ga notif yuborish
        if ($ticket->telegram_id) {
            $this->sendMessage($ticket->telegram_id,
                "✅ <b>Ticket #{$ticket->id} ga javob keldi:</b>\n\n" .
                "<b>Sarlavha:</b> " . htmlspecialchars($ticket->subject, ENT_QUOTES) . "\n\n" .
                "<b>Javob:</b>\n" . htmlspecialchars($reply, ENT_QUOTES) . "\n\n" .
                "🌐 Batafsil: https://cloudapi.uz/dashboard/tickets/{$ticket->id}"
            );
        }

        $this->sendMessage($chatId, 
            "✅ Javob yuborildi va user'ga xabar ketdi.\n\n" .
            "/admin — panel'ga qaytish"
        );
    }

    /**
     * Ticket yopish
     */
    public function closeTicket(int $chatId, int $telegramId, int $messageId, int $ticketId): void
    {
        if (!$this->isAdmin($telegramId)) return;

        $ticket = Ticket::find($ticketId);
        if (!$ticket) return;

        $ticket->update([
            'status' => 'closed',
            'closed_at' => now(),
        ]);

        $this->answerCallback($chatId, "Ticket #{$ticketId} yopildi ✅");

        // Menyuga qaytarish
        $this->tickets($chatId, $telegramId, $messageId);
    }

    /**
     * Statistika
     */
    public function stats(int $chatId, int $telegramId, int $messageId): void
    {
        if (!$this->isAdmin($telegramId)) return;

        $stats = $this->getStats();

        $text = "📊 <b>Batafsil statistika</b>\n\n";
        $text .= "👥 <b>Foydalanuvchilar:</b>\n";
        $text .= "• Jami: <b>{$stats['total_users']}</b>\n";
        $text .= "• Bugun ro'yxatdan o'tgan: <b>{$stats['new_users_today']}</b>\n";
        $text .= "• Ushbu hafta: <b>{$stats['new_users_week']}</b>\n";
        $text .= "• Ushbu oy: <b>{$stats['new_users_month']}</b>\n\n";

        $text .= "💰 <b>Daromad:</b>\n";
        $text .= "• Bugun: <b>" . number_format($stats['revenue_today']) . " UZS</b>\n";
        $text .= "• Ushbu hafta: <b>" . number_format($stats['revenue_week']) . " UZS</b>\n";
        $text .= "• Ushbu oy: <b>" . number_format($stats['revenue_month']) . " UZS</b>\n\n";

        $text .= "🎫 <b>Ticketlar:</b>\n";
        $text .= "• Ochiq: <b>{$stats['open_tickets']}</b>\n";
        $text .= "• Javob berilgan: <b>{$stats['answered_tickets']}</b>\n";
        $text .= "• Jami: <b>{$stats['total_tickets']}</b>\n\n";

        $text .= "💬 <b>Feedbacks:</b>\n";
        $text .= "• Jami: <b>{$stats['total_feedbacks']}</b>\n";
        $text .= "• O'rtacha baho: <b>{$stats['avg_rating']}/5</b>";

        $keyboard = [
            [['text' => '🔄 Yangilash', 'callback_data' => 'admin_stats']],
            [['text' => '⬅️ Orqaga', 'callback_data' => 'admin_menu']],
        ];

        $this->editMessage($chatId, $messageId, $text, [
            'reply_markup' => json_encode(['inline_keyboard' => $keyboard])
        ]);
    }

    /**
     * Umumiy statistika (helper)
     */
    protected function getStats(): array
    {
        $today = now()->startOfDay();
        $weekStart = now()->startOfWeek();
        $monthStart = now()->startOfMonth();

        return [
            'total_users' => User::count(),
            'new_users_today' => User::where('created_at', '>=', $today)->count(),
            'new_users_week' => User::where('created_at', '>=', $weekStart)->count(),
            'new_users_month' => User::where('created_at', '>=', $monthStart)->count(),

            'total_tickets' => Ticket::count(),
            'open_tickets' => Ticket::whereIn('status', ['open', 'in_progress'])->count(),
            'answered_tickets' => Ticket::where('status', 'answered')->count(),
            'new_tickets_today' => Ticket::where('created_at', '>=', $today)->count(),

            'total_feedbacks' => Feedback::count(),
            'avg_rating' => round(Feedback::avg('rating') ?? 0, 1),

            'revenue_today' => $this->getRevenue($today),
            'revenue_week' => $this->getRevenue($weekStart),
            'revenue_month' => $this->getRevenue($monthStart),
            'new_payments_today' => $this->getPaymentCount($today),
        ];
    }

    protected function getRevenue($since): float
    {
        try {
            return (float) DB::table('transactions')
                ->where('type', 'deposit')
                ->where('status', 'completed')
                ->where('created_at', '>=', $since)
                ->sum('amount_uzs');
        } catch (\Exception $e) {
            return 0;
        }
    }

    protected function getPaymentCount($since): int
    {
        try {
            return DB::table('transactions')
                ->where('type', 'deposit')
                ->where('status', 'completed')
                ->where('created_at', '>=', $since)
                ->count();
        } catch (\Exception $e) {
            return 0;
        }
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

    protected function answerCallback(int|string $chatId, string $text): void
    {
        // Callback query'ga javob berish (bot API)
        // Bu funksiya BotWebhookController orqali chaqiriladi
    }
}