<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ChatMessage;
use App\Models\ChatSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Admin — barcha foydalanuvchilar chatlarini nazorat qilish (read-only).
 */
class ChatController extends Controller
{
    public function index(Request $request)
    {
        $stats = [
            'sessions' => ChatSession::count(),
            'messages' => ChatMessage::count(),
            'cost'     => (float) ChatMessage::sum('cost_uzs'),
            'users'    => ChatSession::distinct('user_id')->count('user_id'),
            'today'    => ChatMessage::whereDate('created_at', today())->count(),
        ];

        // Eng ko'p ishlatilgan modellar
        $topModels = ChatMessage::where('role', 'assistant')
            ->whereNotNull('model_id')->where('model_id', '!=', '')
            ->select('model_id', DB::raw('COUNT(*) as cnt'), DB::raw('SUM(cost_uzs) as cost'))
            ->groupBy('model_id')
            ->orderByDesc('cnt')
            ->limit(8)
            ->get();

        $query = ChatSession::with('user')
            ->withCount('messages')
            ->orderByDesc('last_message_at')
            ->orderByDesc('created_at');

        if ($q = $request->get('q')) {
            $query->where(function ($sq) use ($q) {
                $sq->where('title', 'like', "%{$q}%")
                   ->orWhereHas('user', function ($uq) use ($q) {
                       $uq->where('name', 'like', "%{$q}%")->orWhere('email', 'like', "%{$q}%");
                   });
            });
        }
        if ($model = $request->get('model')) {
            $query->where('model_id', $model);
        }

        $sessions = $query->paginate(30)->withQueryString();

        return view('admin.chat.index', compact('stats', 'topModels', 'sessions'));
    }

    public function show(int $sessionId)
    {
        $session = ChatSession::with('user')->findOrFail($sessionId);
        $messages = $session->messages()->with('attachments')->orderBy('created_at')->get();

        return view('admin.chat.show', compact('session', 'messages'));
    }
}
