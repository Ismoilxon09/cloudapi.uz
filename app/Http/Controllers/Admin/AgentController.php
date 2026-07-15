<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Agent;
use App\Models\AgentChannel;
use App\Models\AgentMessage;
use App\Services\Agent\AgentTelegram;
use Illuminate\Http\Request;

/**
 * Admin — barcha foydalanuvchi agentlarini nazorat va boshqarish.
 */
class AgentController extends Controller
{
    public function index(Request $request)
    {
        $stats = [
            'agents'    => Agent::count(),
            'active'    => Agent::where('status', 'active')->count(),
            'telegram'  => AgentChannel::where('type', 'telegram')->where('status', 'active')->count(),
            'replies'   => (int) Agent::sum('total_replies'),
            'spent'     => (float) Agent::sum('total_spent_uzs'),
            'today'     => AgentMessage::whereDate('created_at', today())->where('role', 'assistant')->count(),
        ];

        $query = Agent::with(['user', 'telegramChannel'])
            ->withCount('conversations')
            ->orderByDesc('last_active_at')
            ->orderByDesc('created_at');

        if ($q = $request->get('q')) {
            $query->where(function ($sq) use ($q) {
                $sq->where('name', 'like', "%{$q}%")
                   ->orWhereHas('user', fn ($uq) => $uq->where('name', 'like', "%{$q}%")->orWhere('email', 'like', "%{$q}%"));
            });
        }
        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        $agents = $query->paginate(30)->withQueryString();

        return view('admin.agents.index', compact('stats', 'agents'));
    }

    public function show(Agent $agent)
    {
        $agent->load(['user', 'telegramChannel', 'apiChannel', 'webChannel', 'mcpServers']);

        $conversations = $agent->conversations()
            ->orderByDesc('last_message_at')->orderByDesc('id')
            ->limit(30)->get();

        $recentMessages = $agent->messages()
            ->orderByDesc('id')->limit(40)->get();

        return view('admin.agents.show', compact('agent', 'conversations', 'recentMessages'));
    }

    public function toggleStatus(Agent $agent)
    {
        $agent->update(['status' => $agent->status === 'active' ? 'paused' : 'active']);
        return back()->with('success', "Agent holati: {$agent->status}");
    }

    public function destroy(Agent $agent)
    {
        if ($ch = $agent->telegramChannel) {
            if ($token = $ch->getTelegramToken()) {
                try { (new AgentTelegram($token))->deleteWebhook(); } catch (\Throwable $e) {}
            }
        }
        $name = $agent->name;
        $agent->delete();

        return redirect()->route('admin.agents.index')->with('success', "Agent o'chirildi: {$name}");
    }
}
