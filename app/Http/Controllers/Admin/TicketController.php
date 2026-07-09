<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TicketController extends Controller
{
    /**
     * Admin ticket ro'yxati (filter bilan)
     */
    public function index(Request $request)
    {
        $query = Ticket::with('user');

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by priority
        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        // Filter by source
        if ($request->filled('source')) {
            $query->where('source', $request->source);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('subject', 'like', "%{$search}%")
                  ->orWhere('message', 'like', "%{$search}%")
                  ->orWhere('id', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($u) use ($search) {
                      $u->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        // Statistics
        $stats = [
            'total' => Ticket::count(),
            'open' => Ticket::where('status', 'open')->count(),
            'in_progress' => Ticket::where('status', 'in_progress')->count(),
            'answered' => Ticket::where('status', 'answered')->count(),
            'closed' => Ticket::where('status', 'closed')->count(),
            'urgent' => Ticket::where('priority', 'urgent')->whereIn('status', ['open', 'in_progress'])->count(),
        ];

        $tickets = $query->orderByDesc('created_at')->paginate(25);

        return view('admin.tickets.index', compact('tickets', 'stats'));
    }

    /**
     * Bitta ticket'ni ko'rish (admin)
     */
    public function show(Ticket $ticket)
    {
        $ticket->load('user', 'admin');
        return view('admin.tickets.show', compact('ticket'));
    }
    
    /**
     * Ticket'ga javob berish
     */
    public function reply(Request $request, Ticket $ticket)
    {
        $validated = $request->validate([
            'admin_reply' => 'required|string|min:5|max:5000',
            'status' => 'nullable|in:open,in_progress,answered,closed',
        ]);

        $ticket->update([
            'admin_reply' => trim($validated['admin_reply']),
            'admin_id' => auth()->id(),
            'status' => $validated['status'] ?? 'answered',
            'replied_at' => now(),
        ]);

        // User'ga Telegram xabar
        $this->notifyUser($ticket);

        return redirect()->route('admin.tickets.show', $ticket->id)
            ->with('success', 'Javob yuborildi va foydalanuvchiga xabar ketdi.');
    }

    /**
     * Ticket status o'zgartirish (tez amal)
     */
    public function updateStatus(Request $request, Ticket $ticket)
    {
        $validated = $request->validate([
            'status' => 'required|in:open,in_progress,answered,closed',
        ]);

        $data = ['status' => $validated['status']];

        if ($validated['status'] === 'closed') {
            $data['closed_at'] = now();
        }

        $ticket->update($data);

        return back()->with('success', 'Holat yangilandi.');
    }

    /**
     * Priority o'zgartirish
     */
     public function updatePriority(Request $request, Ticket $ticket)
    {
        $validated = $request->validate([
            'priority' => 'required|in:low,normal,high,urgent',
        ]);

        $ticket->update(['priority' => $validated['priority']]);

        return back()->with('success', 'Muhimlik yangilandi.');
    }
    /**
     * Ticket o'chirish (spam holatlarida)
     */
    public function destroy(Ticket $ticket)
    {
        $ticket->delete();
        return redirect()->route('admin.tickets.index')
            ->with('success', 'Ticket o\'chirildi.');
    }

    /**
     * User'ga javob haqida Telegram xabari
     */
    protected function notifyUser(Ticket $ticket): void
    {
        if (!$ticket->telegram_id) return;

        try {
            $botToken = env('TELEGRAM_BOT_TOKEN');
            if (!$botToken) return;

            $subject = htmlspecialchars($ticket->subject, ENT_QUOTES);
            $reply = htmlspecialchars(mb_substr($ticket->admin_reply, 0, 500), ENT_QUOTES);

            $text = "✅ <b>Ticket #{$ticket->id} ga javob keldi</b>\n\n";
            $text .= "<b>Sarlavha:</b>\n{$subject}\n\n";
            $text .= "<b>Javob:</b>\n{$reply}\n\n";
            $text .= "🌐 To'liq: https://cloudapi.uz/dashboard/tickets/{$ticket->id}";

            Http::timeout(3)->post(
                "https://api.telegram.org/bot{$botToken}/sendMessage",
                [
                    'chat_id' => $ticket->telegram_id,
                    'text' => $text,
                    'parse_mode' => 'HTML',
                    'disable_web_page_preview' => true,
                ]
            );
        } catch (\Exception $e) {
            Log::warning("Admin reply notify failed: " . $e->getMessage());
        }
    }
}