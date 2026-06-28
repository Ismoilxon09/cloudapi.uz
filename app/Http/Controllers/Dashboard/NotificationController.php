<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\BotNotification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * Notifications sahifa
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $query = BotNotification::where('user_id', $user->id)
            ->orderBy('created_at', 'desc');

        // Filter
        $filter = $request->get('filter', 'all');
        if ($filter === 'unread') {
            $query->whereNull('read_at');
        }

        $notifications = $query->paginate(20);

        $stats = [
            'total' => BotNotification::where('user_id', $user->id)->count(),
            'unread' => BotNotification::where('user_id', $user->id)->whereNull('read_at')->count(),
        ];

        return view('dashboard.notifications.index', compact('notifications', 'stats', 'filter'));
    }

    /**
     * Bildirishnomani o'qilgan deb belgilash
     */
    public function markRead(Request $request, $id)
    {
        $notification = BotNotification::where('user_id', $request->user()->id)
            ->where('id', $id)
            ->firstOrFail();

        $notification->markAsRead();

        if ($request->wantsJson()) {
            return response()->json(['ok' => true]);
        }

        // Action URL bo'lsa — o'sha sahifaga yo'naltirish
        if ($notification->action_url) {
            return redirect($notification->action_url);
        }

        return back();
    }

    /**
     * Hammasini o'qilgan deb belgilash
     */
    public function markAllRead(Request $request)
    {
        BotNotification::where('user_id', $request->user()->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        if ($request->wantsJson()) {
            return response()->json(['ok' => true]);
        }

        return back()->with('success', 'Hammasi o\'qilgan deb belgilandi');
    }

    /**
     * O'chirish
     */
    public function destroy(Request $request, $id)
    {
        BotNotification::where('user_id', $request->user()->id)
            ->where('id', $id)
            ->delete();

        if ($request->wantsJson()) {
            return response()->json(['ok' => true]);
        }

        return back()->with('success', 'O\'chirildi');
    }

    /**
     * Header'da ko'rsatish uchun (dropdown)
     * Oxirgi 5 ta + o'qilmagan soni
     */
    public function dropdown(Request $request)
    {
        $user = $request->user();

        $notifications = BotNotification::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        $unreadCount = BotNotification::where('user_id', $user->id)
            ->whereNull('read_at')
            ->count();

        return response()->json([
            'unread_count' => $unreadCount,
            'notifications' => $notifications->map(fn($n) => [
                'id' => $n->id,
                'icon' => $n->getDisplayIcon(),
                'title' => $n->getDisplayTitle(),
                'message' => $n->message,
                'color' => $n->getColorClass(),
                'time' => $n->getTimeAgo(),
                'is_read' => !is_null($n->read_at),
                'action_url' => $n->action_url,
            ]),
        ]);
    }
}