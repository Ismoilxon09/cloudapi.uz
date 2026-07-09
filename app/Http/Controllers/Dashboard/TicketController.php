<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TicketController extends Controller
{
    /**
     * User'ning ticketlar ro'yxati
     */
    public function index()
    {
        $user = auth()->user();

        $tickets = Ticket::query()
            ->where(function ($q) use ($user) {
                $q->where('user_id', $user->id);
                if ($user->telegram_id) {
                    $q->orWhere('telegram_id', $user->telegram_id);
                }
            })
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('dashboard.tickets.index', compact('tickets'));
    }

    /**
     * Yangi ticket yaratish formasi
     */
    public function create()
    {
        return view('dashboard.tickets.create');
    }

    /**
     * Ticket saqlash
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'subject' => 'required|string|min:5|max:255',
            'message' => 'required|string|min:10|max:2000',
            'priority' => 'nullable|in:low,normal,high,urgent',
        ], [
            'subject.required' => 'Sarlavha kiriting',
            'subject.min' => 'Sarlavha kamida 5 belgi bo\'lsin',
            'message.required' => 'Xabar kiriting',
            'message.min' => 'Xabar kamida 10 belgi bo\'lsin',
        ]);

        $user = auth()->user();

        $ticket = Ticket::create([
            'user_id' => $user->id,
            'telegram_id' => $user->telegram_id,
            'subject' => strip_tags($validated['subject']),
            'message' => strip_tags($validated['message']),
            'status' => 'open',
            'priority' => $validated['priority'] ?? 'normal',
            'source' => 'web',
        ]);

        // Admin'ga notif
        $this->notifyAdmin($ticket);

        return redirect()->route('dashboard.tickets.show', $ticket->id)
            ->with('success', 'Ticket yaratildi. Tez orada javob olasiz.');
    }

    /**
     * Bitta ticket ko'rish
     */
    public function show(Ticket $ticket)
    {
        $user = auth()->user();

        // Xavfsizlik: faqat o'zining ticketini ko'rishi mumkin
        if ($ticket->user_id !== $user->id && $ticket->telegram_id !== $user->telegram_id) {
            abort(403, 'Bu ticket sizga tegishli emas');
        }

        return view('dashboard.tickets.show', compact('ticket'));
    }

    /**
     * Admin'ga Telegram xabar
     */
    protected function notifyAdmin(Ticket $ticket): void
    {
        try {
            $adminChatId = env('TELEGRAM_ADMIN_CHAT_ID');
            $botToken = env('TELEGRAM_BOT_TOKEN');

            if (!$adminChatId || !$botToken) return;

            $userName = $ticket->user?->name ?? 'Foydalanuvchi';
            $subject = htmlspecialchars($ticket->subject, ENT_QUOTES);
            $message = htmlspecialchars(mb_substr($ticket->message, 0, 300), ENT_QUOTES);

            $text = "🎫 <b>Yangi ticket #{$ticket->id}</b>\n\n";
            $text .= "<b>Kimdan:</b> {$userName}\n";
            $text .= "<b>Manba:</b> Web\n";
            $text .= "<b>Muhimlik:</b> " . htmlspecialchars($ticket->priority_label, ENT_QUOTES) . "\n\n";
            $text .= "<b>Sarlavha:</b>\n{$subject}\n\n";
            $text .= "<b>Xabar:</b>\n{$message}";

            Http::timeout(3)->post(
                "https://api.telegram.org/bot{$botToken}/sendMessage",
                [
                    'chat_id' => $adminChatId,
                    'text' => $text,
                    'parse_mode' => 'HTML',
                    'disable_web_page_preview' => true,
                    'reply_markup' => json_encode([
                        'inline_keyboard' => [[
                            ['text' => '💬 Javob berish', 'callback_data' => "ticket_reply:{$ticket->id}"],
                            ['text' => '🔒 Yopish', 'callback_data' => "ticket_close:{$ticket->id}"],
                        ]]
                    ])
                ]
            );
        } catch (\Exception $e) {
            Log::warning("Admin ticket notify failed: " . $e->getMessage());
        }
    }
}                                                                               