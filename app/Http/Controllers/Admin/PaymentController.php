<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminLog;
use App\Models\AdminNotification;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    /**
     * Manual to'lovlar ro'yxati
     */
    public function index(Request $request)
    {
        $status = $request->get('status', 'pending');
        $search = $request->get('q');

        $query = Transaction::with('user')
            ->where('type', 'deposit')
            ->where('payment_method', 'manual');

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        if ($search) {
            $query->whereHas('user', function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $payments = $query->latest()->paginate(20)->withQueryString();

        // Counters
        $counts = [
            'pending' => Transaction::where('type', 'deposit')->where('payment_method', 'manual')->where('status', 'pending')->count(),
            'completed' => Transaction::where('type', 'deposit')->where('payment_method', 'manual')->where('status', 'completed')->count(),
            'failed' => Transaction::where('type', 'deposit')->where('payment_method', 'manual')->where('status', 'failed')->count(),
            'all' => Transaction::where('type', 'deposit')->where('payment_method', 'manual')->count(),
        ];

        return view('admin.payments.index', compact('payments', 'status', 'counts'));
    }

    /**
     * Bitta to'lov batafsil
     */
    public function show(Transaction $tx)
    {
        $tx->load('user');

        // User'ning oxirgi 5 ta tranzaksiya
        $userHistory = Transaction::where('user_id', $tx->user_id)
            ->where('id', '!=', $tx->id)
            ->latest()
            ->limit(5)
            ->get();

        return view('admin.payments.show', compact('tx', 'userHistory'));
    }

    /**
     * To'lovni tasdiqlash → balansga qo'shish
     */
    public function approve(Request $request, Transaction $tx)
    {
        if ($tx->status !== 'pending') {
            return back()->with('error', "Bu to'lov allaqachon ko'rib chiqilgan");
        }

        $validated = $request->validate([
            'admin_note' => 'nullable|string|max:500',
        ]);

        DB::transaction(function () use ($tx, $validated) {
            $wallet = $tx->user->wallet;

            if (!$wallet) {
                $wallet = $tx->user->wallet()->create([
                    'balance_uzs' => 0,
                    'total_deposited' => 0,
                    'total_spent' => 0,
                ]);
            }

            // Balansga qo'shish
            $wallet->increment('balance_uzs', $tx->amount_uzs);
            $wallet->increment('total_deposited', $tx->amount_uzs);

            // Tranzaksiya yangilash
            $tx->update([
                'status' => 'completed',
                'balance_after' => $wallet->fresh()->balance_uzs,
                'admin_id' => auth()->id(),
                'admin_note' => $validated['admin_note'] ?? null,
                'reviewed_at' => now(),
            ]);

            // Admin log
            AdminLog::record(
                'payment_approved',
                $tx,
                "Tasdiqlandi: {$tx->user->name} → " . number_format($tx->amount_uzs, 0, '.', ' ') . " so'm",
                ['amount' => $tx->amount_uzs, 'user_id' => $tx->user_id]
            );
        });

        // Telegram orqali userga xabar (agar telegram_chat_id bo'lsa)
        $this->notifyUser($tx);

        return redirect()->route('admin.payments.index')
            ->with('success', "To'lov tasdiqlandi va " . number_format($tx->amount_uzs, 0, '.', ' ') . " so'm balansga qo'shildi");
    }

    /**
     * To'lovni rad etish
     */
    public function reject(Request $request, Transaction $tx)
    {
        if ($tx->status !== 'pending') {
            return back()->with('error', "Bu to'lov allaqachon ko'rib chiqilgan");
        }

        $validated = $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ]);

        $tx->update([
            'status' => 'failed',
            'admin_id' => auth()->id(),
            'rejection_reason' => $validated['rejection_reason'],
            'reviewed_at' => now(),
        ]);

        AdminLog::record(
            'payment_rejected',
            $tx,
            "Rad etildi: {$tx->user->name} → " . number_format($tx->amount_uzs, 0, '.', ' ') . " so'm. Sabab: {$validated['rejection_reason']}",
            ['amount' => $tx->amount_uzs, 'reason' => $validated['rejection_reason']]
        );

        return redirect()->route('admin.payments.index')
            ->with('success', "To'lov rad etildi");
    }

    /**
     * Userga Telegram orqali xabar
     */
    protected function notifyUser(Transaction $tx): void
    {
        if (!$tx->user->telegram_chat_id) return;

        $token = \App\Models\SystemSetting::get('telegram_bot_token');
        if (!$token) return;

        $text = "✅ *To'lov tasdiqlandi*\n\n"
            . "Miqdor: *" . number_format($tx->amount_uzs, 0, '.', ' ') . " so'm*\n"
            . "Hozirgi balans: *" . number_format($tx->user->wallet->balance_uzs, 0, '.', ' ') . " so'm*\n\n"
            . "Rahmat! Endi API dan foydalanishingiz mumkin.";

        try {
            \Illuminate\Support\Facades\Http::post("https://api.telegram.org/bot{$token}/sendMessage", [
                'chat_id' => $tx->user->telegram_chat_id,
                'text' => $text,
                'parse_mode' => 'Markdown',
            ]);
        } catch (\Exception $e) {
            \Log::error('Telegram user notify failed: ' . $e->getMessage());
        }
    }
}