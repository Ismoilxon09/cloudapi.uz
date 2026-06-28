<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminNotification;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $query = AdminNotification::query();

        if ($filter = $request->get('filter')) {
            match($filter) {
                'unread' => $query->whereNull('read_at'),
                'urgent' => $query->where('priority', 'urgent'),
                'today' => $query->whereDate('created_at', today()),
                default => null,
            };
        }

        if ($type = $request->get('type')) {
            $query->where('type', $type);
        }

        $notifications = $query->latest()->paginate(30);

        $counts = [
            'all' => AdminNotification::count(),
            'unread' => AdminNotification::whereNull('read_at')->count(),
            'urgent' => AdminNotification::where('priority', 'urgent')->count(),
        ];

        return view('admin.notifications.index', compact('notifications', 'counts'));
    }

    public function unread(): JsonResponse
    {
        return response()->json([
            'count' => AdminNotification::whereNull('read_at')->count(),
            'notifications' => AdminNotification::whereNull('read_at')->latest()->limit(10)->get(),
        ]);
    }

    public function markRead(AdminNotification $notif)
    {
        $notif->markRead();
        return back();
    }

    public function markAllRead()
    {
        AdminNotification::whereNull('read_at')->update([
            'read_at' => now(),
            'read_by' => auth()->id(),
        ]);
        return back()->with('success', 'Hamma bildirishnomalar o\'qildi deb belgilandi');
    }
}