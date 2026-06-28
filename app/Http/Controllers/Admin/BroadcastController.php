<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class BroadcastController extends Controller
{
    public function index()
    {
        $broadcasts = DB::table('broadcasts')->orderByDesc('created_at')->paginate(20);
        return view('admin.broadcasts.index', compact('broadcasts'));
    }

    public function create()
    {
        $totalUsers = User::where('role', 'user')->count();
        $activeUsers = User::where('role', 'user')->where('status', 'active')->count();
        $telegramUsers = User::where('role', 'user')->whereNotNull('telegram_chat_id')->count();

        return view('admin.broadcasts.create', compact('totalUsers', 'activeUsers', 'telegramUsers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'channel' => 'required|in:telegram,in_app',
            'target' => 'required|in:all,active',
            'subject' => 'nullable|string|max:255',
            'message' => 'required|string|max:4000',
        ]);

        // Get recipients
        $query = User::where('role', 'user');
        if ($validated['target'] === 'active') {
            $query->where('status', 'active');
        }

        if ($validated['channel'] === 'telegram') {
            $query->whereNotNull('telegram_chat_id');
        }

        $recipients = $query->get();

        // Create broadcast record
        $broadcastId = DB::table('broadcasts')->insertGetId([
            'admin_id' => auth()->id(),
            'channel' => $validated['channel'],
            'target' => $validated['target'],
            'subject' => $validated['subject'],
            'message' => $validated['message'],
            'status' => 'sending',
            'total_recipients' => $recipients->count(),
            'started_at' => now(),
            'created_at' => now(),
        ]);

        // Send (synchronously for MVP)
        $sent = 0;
        $failed = 0;

        if ($validated['channel'] === 'telegram') {
            $token = \App\Models\SystemSetting::get('telegram_bot_token');
            if (!$token) {
                return back()->with('error', 'Telegram bot token sozlanmagan');
            }

            foreach ($recipients as $user) {
                try {
                    Http::post("https://api.telegram.org/bot{$token}/sendMessage", [
                        'chat_id' => $user->telegram_chat_id,
                        'text' => $validated['message'],
                        'parse_mode' => 'Markdown',
                    ]);
                    $sent++;
                } catch (\Exception $e) {
                    $failed++;
                }
            }
        }

        // Update broadcast
        DB::table('broadcasts')->where('id', $broadcastId)->update([
            'status' => $failed > 0 ? 'sent' : 'sent',
            'sent_count' => $sent,
            'failed_count' => $failed,
            'completed_at' => now(),
        ]);

        AdminLog::record('broadcast_sent', null,
            "Xabar yuborildi: {$sent}/{$recipients->count()} ({$validated['channel']})",
            ['sent' => $sent, 'failed' => $failed, 'channel' => $validated['channel']]
        );

        return redirect()->route('admin.broadcasts.index')
            ->with('success', "Yuborildi: {$sent} ta foydalanuvchi" . ($failed > 0 ? ", {$failed} ta xato" : ''));
    }
}