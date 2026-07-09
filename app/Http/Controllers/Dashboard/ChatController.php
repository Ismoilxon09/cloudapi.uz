<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\AiModel;
use App\Models\ChatMessage;
use App\Models\ChatSession;
use App\Services\Chat\ChatOrchestrator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    /**
     * Chat sahifasi (agar id bo'lsa - o'sha session, bo'lmasa yangi)
     */
    public function index(Request $request, ?int $sessionId = null, bool $admin = false)
    {
        $user = auth()->user();

        // Session'lar ro'yxati (sidebar uchun)
        $sessions = ChatSession::where('user_id', $user->id)
            ->where('is_archived', 0)
            ->orderByDesc('is_pinned')
            ->orderByDesc('last_message_at')
            ->orderByDesc('created_at')
            ->limit(50)
            ->get();

        // Aktiv session
        $currentSession = null;
        $messages = collect();

        if ($sessionId) {
            $currentSession = ChatSession::where('id', $sessionId)
                ->where('user_id', $user->id)
                ->first();

            if ($currentSession) {
                $messages = $currentSession->messages()->with('attachments')->get();
            }
        }

        // Modellar ro'yxati (dropdown uchun)
        $models = AiModel::where('active', 1)
            ->orderByDesc('is_featured')
            ->orderBy('provider')
            ->orderBy('display_name')
            ->get();

        // Balans
        $wallet = $user->wallet;
        $balance = $wallet ? ($wallet->balance_uzs + $wallet->bonus_balance_uzs) : 0;

        // Navigatsiya konteksti (dashboard yoki admin panel).
        // Action endpointlar (stream/regenerate/...) doim /dashboard/chat'da qoladi.
        $navBase = $admin ? url('/admin/chat') : url('/dashboard/chat');
        $actionBase = url('/dashboard/chat');
        $indexUrl = $navBase;
        $backUrl = $admin ? route('admin.dashboard') : route('dashboard');
        $backLabel = $admin ? 'Admin panel' : 'Asosiy panel';

        return view('dashboard.chat.index', compact(
            'sessions', 'currentSession', 'messages', 'models', 'balance',
            'navBase', 'actionBase', 'indexUrl', 'backUrl', 'backLabel'
        ));
    }

    /**
     * Admin panel ichidagi chat (bir xil ChatController, admin konteksti bilan)
     */
    public function adminIndex(Request $request)
    {
        return $this->index($request, null, true);
    }

    public function adminShow(int $sessionId, Request $request)
    {
        return $this->index($request, $sessionId, true);
    }

    /**
     * Yangi session yaratish
     */
    public function create(Request $request): JsonResponse
    {
        $user = auth()->user();

        $session = ChatSession::create([
            'user_id' => $user->id,
            'title' => 'Yangi chat',
            'model_id' => $request->get('model_id'),
        ]);

        return response()->json([
            'ok' => true,
            'session' => [
                'id' => $session->id,
                'title' => $session->title,
                'url' => route('dashboard.chat.show', $session->id),
            ],
        ]);
    }

    /**
     * Bitta session ochish
     */
    public function show(int $sessionId, Request $request)
    {
        return $this->index($request, $sessionId);
    }

    /**
     * Xabar yuborish (AJAX)
     */
    public function sendMessage(Request $request, ChatOrchestrator $orchestrator): JsonResponse
    {
        $validated = $request->validate([
            'session_id' => 'nullable|integer|exists:chat_sessions,id',
            'model_id' => 'required|string',
            'content' => 'required_without:images|nullable|string|max:20000',
            'temperature' => 'nullable|numeric|min:0|max:2',
            'max_tokens' => 'nullable|integer|min:1|max:32000',
            'images' => 'nullable|array|max:4',
            'images.*.data' => 'required|string',
            'images.*.name' => 'nullable|string|max:255',
            'images.*.mime' => 'nullable|string|max:100',
        ]);

        $user = auth()->user();

        // Session — yaratish yoki mavjud
        if (!empty($validated['session_id'])) {
            $session = ChatSession::where('id', $validated['session_id'])
                ->where('user_id', $user->id)
                ->firstOrFail();
        } else {
            $session = ChatSession::create([
                'user_id' => $user->id,
                'title' => 'Yangi chat',
                'model_id' => $validated['model_id'],
                'temperature' => $validated['temperature'] ?? 0.7,
            ]);
        }

        try {
            $result = $orchestrator->sendMessage(
                $session,
                $validated['content'] ?? '',
                $validated['model_id'],
                $validated['temperature'] ?? null,
                $validated['max_tokens'] ?? null,
                $validated['images'] ?? [],
            );

            // Yangi balans
            $wallet = $user->wallet()->first();
            $newBalance = $wallet ? ($wallet->balance_uzs + $wallet->bonus_balance_uzs) : 0;

            return response()->json([
                'ok' => true,
                'session_id' => $session->id,
                'session_title' => $session->title,
                'message' => [
                    'id' => $result['message']->id,
                    'role' => $result['message']->role,
                    'content' => $result['message']->content,
                    'model_id' => $result['message']->model_id,
                    'cost_uzs' => $result['cost_uzs'] ?? 0,
                    'tokens_input' => $result['tokens_input'] ?? 0,
                    'tokens_output' => $result['tokens_output'] ?? 0,
                    'created_at' => $result['message']->created_at->toIso8601String(),
                ],
                'new_balance' => $newBalance,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'ok' => false,
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Session'ni o'chirish
     */
    public function destroy(int $sessionId): JsonResponse
    {
        $session = ChatSession::where('id', $sessionId)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        $session->delete();

        return response()->json(['ok' => true]);
    }

    /**
     * Session'ni pin qilish/olib tashlash
     */
    public function togglePin(int $sessionId): JsonResponse
    {
        $session = ChatSession::where('id', $sessionId)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        $session->update(['is_pinned' => !$session->is_pinned]);

        return response()->json(['ok' => true, 'is_pinned' => $session->is_pinned]);
    }

    /**
     * Session sarlavhasini o'zgartirish
     */
    public function updateTitle(Request $request, int $sessionId): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:100',
        ]);

        $session = ChatSession::where('id', $sessionId)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        $session->update(['title' => strip_tags($validated['title'])]);

        return response()->json(['ok' => true]);
    }

    public function streamMessage(Request $request, ChatOrchestrator $orchestrator)
    {
        $validated = $request->validate([
            'session_id' => 'nullable|integer|exists:chat_sessions,id',
            'model_id' => 'required|string',
            'content' => 'required_without:images|nullable|string|max:20000',
            'temperature' => 'nullable|numeric|min:0|max:2',
            'max_tokens' => 'nullable|integer|min:1|max:32000',
            'images' => 'nullable|array|max:4',
            'images.*.data' => 'required|string',
            'images.*.name' => 'nullable|string|max:255',
            'images.*.mime' => 'nullable|string|max:100',
        ]);
    
        $user = auth()->user();
    
        // Session — yaratish yoki mavjud
        if (!empty($validated['session_id'])) {
            $session = ChatSession::where('id', $validated['session_id'])
                ->where('user_id', $user->id)
                ->firstOrFail();
        } else {
            $session = ChatSession::create([
                'user_id' => $user->id,
                'title' => 'Yangi chat',
                'model_id' => $validated['model_id'],
                'temperature' => $validated['temperature'] ?? 0.7,
            ]);
        }
    
        // SSE response
        return response()->stream(function () use ($session, $validated, $orchestrator) {
            // Buffering o'chirilishi kerak
            if (ob_get_level()) ob_end_clean();
    
            $orchestrator->streamMessage(
                $session,
                $validated['content'] ?? '',
                $validated['model_id'],
                function ($chunk) {
                    echo "data: " . json_encode($chunk) . "\n\n";

                    if (ob_get_level()) ob_flush();
                    flush();
                },
                $validated['temperature'] ?? null,
                $validated['max_tokens'] ?? null,
                $validated['images'] ?? [],
            );
    
            echo "data: [DONE]\n\n";
            if (ob_get_level()) ob_flush();
            flush();
        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache',
            'X-Accel-Buffering' => 'no',
            'Connection' => 'keep-alive',
        ]);
    }

    /**
     * Oxirgi javobni qayta yaratish (SSE)
     */
    public function regenerate(int $sessionId, Request $request, ChatOrchestrator $orchestrator)
    {
        $session = ChatSession::where('id', $sessionId)->where('user_id', auth()->id())->firstOrFail();
        $v = $request->validate([
            'model_id' => 'nullable|string',
            'temperature' => 'nullable|numeric|min:0|max:2',
            'max_tokens' => 'nullable|integer|min:1|max:32000',
        ]);

        return $this->sseStream(fn ($emit) => $orchestrator->streamRegenerate(
            $session, $emit, $v['model_id'] ?? null, $v['temperature'] ?? null, $v['max_tokens'] ?? null
        ));
    }

    /**
     * User xabarni tahrirlab qayta yuborish (SSE)
     */
    public function editMessage(int $sessionId, Request $request, ChatOrchestrator $orchestrator)
    {
        $session = ChatSession::where('id', $sessionId)->where('user_id', auth()->id())->firstOrFail();
        $v = $request->validate([
            'message_id' => 'required|integer',
            'content' => 'required|string|max:20000',
            'model_id' => 'nullable|string',
            'temperature' => 'nullable|numeric|min:0|max:2',
        ]);

        return $this->sseStream(fn ($emit) => $orchestrator->streamEditResend(
            $session, $v['message_id'], $v['content'], $emit, $v['model_id'] ?? null, $v['temperature'] ?? null
        ));
    }

    /**
     * Session sozlamalari — system prompt + temperature
     */
    public function updateSettings(int $sessionId, Request $request): JsonResponse
    {
        $v = $request->validate([
            'system_prompt' => 'nullable|string|max:8000',
            'temperature' => 'nullable|numeric|min:0|max:2',
        ]);
        $session = ChatSession::where('id', $sessionId)->where('user_id', auth()->id())->firstOrFail();
        $session->update([
            'system_prompt' => $v['system_prompt'] ?? null,
            'temperature' => $v['temperature'] ?? $session->temperature,
        ]);
        return response()->json(['ok' => true]);
    }

    /**
     * SSE oqim javobini o'rab beruvchi yordamchi
     */
    private function sseStream(\Closure $producer)
    {
        return response()->stream(function () use ($producer) {
            if (ob_get_level()) ob_end_clean();
            $emit = function ($chunk) {
                echo "data: " . json_encode($chunk) . "\n\n";
                if (ob_get_level()) ob_flush();
                flush();
            };
            $producer($emit);
            echo "data: [DONE]\n\n";
            if (ob_get_level()) ob_flush();
            flush();
        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache',
            'X-Accel-Buffering' => 'no',
            'Connection' => 'keep-alive',
        ]);
    }
}