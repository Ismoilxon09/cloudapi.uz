<?php

namespace App\Services\Agent;

use App\Models\Agent;
use App\Models\AgentConversation;
use App\Models\AgentMessage;
use App\Models\AiModel;
use App\Models\Transaction;
use App\Models\Wallet;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Agentni ishga tushiruvchi yadro: kontekst yig'ish → model chaqirish →
 * javobni saqlash → egasining hamyonidan billing → agent hisoblagichlari.
 *
 * Har kanal (Telegram, web, api) shu servisdan foydalanadi.
 */
class AgentRunner
{
    /** Xatti-harakat presetlari — system promptga qo'shiladigan ko'rsatmalar. */
    protected const PRESETS = [
        'coder'   => "You are an expert coding assistant. Give correct, concise, runnable code with short explanations. Prefer modern idioms.",
        'support' => "You are a customer-support agent. Be polite, warm and concise. Solve the user's problem step by step. If you are unsure, say so honestly and offer to escalate.",
        'sales'   => "You are a sales assistant. Be helpful and persuasive but always honest. Understand the user's need and guide them toward the most relevant option. Never invent facts.",
        'tutor'   => "You are a patient tutor. Explain clearly with simple examples, check understanding, and encourage the learner.",
    ];

    /**
     * Foydalanuvchi xabariga agent javobini yaratadi.
     *
     * @return array{success:bool, content?:string, error?:string, cost_uzs?:float,
     *               tokens_input?:int, tokens_output?:int, model?:string}
     */
    public function reply(Agent $agent, AgentConversation $conversation, string $userText, array $ctx = []): array
    {
        $userText = trim($userText);
        if ($userText === '') {
            return ['success' => false, 'error' => 'empty_message'];
        }

        // 1. Egasi + hamyon
        $owner = $agent->user;
        if (!$owner) {
            return ['success' => false, 'error' => 'owner_missing'];
        }
        $wallet = Wallet::firstOrCreate(['user_id' => $owner->id]);
        if ((float) $wallet->balance_uzs + (float) $wallet->bonus_balance_uzs <= 0) {
            return ['success' => false, 'error' => 'insufficient_balance'];
        }

        // 2. Kunlik sarf limiti (abuse himoyasi)
        if ($agent->isOverDailyCap()) {
            return ['success' => false, 'error' => 'daily_cap_reached'];
        }

        // 3. Model
        $model = $agent->resolveModel();
        if (!$model) {
            return ['success' => false, 'error' => 'model_unavailable'];
        }

        // 4. Xabarlar (system + tarix + yangi user xabari)
        $messages = $this->buildMessages($agent, $conversation, $userText);

        // 5. User xabarini saqlash
        $this->storeMessage($conversation, $agent, 'user', $userText);

        // 6. Model chaqiruvi
        $started = microtime(true);
        $result = $this->callModel($model, $messages, (float) $agent->temperature, $agent->max_tokens);
        $latencyMs = (int) round((microtime(true) - $started) * 1000);

        if (!($result['success'] ?? false)) {
            Log::warning('AgentRunner model call failed', [
                'agent_id' => $agent->id, 'model' => $model->model_id, 'error' => $result['error'] ?? '?',
            ]);
            return ['success' => false, 'error' => $result['error'] ?? 'model_error'];
        }

        $content = trim((string) ($result['content'] ?? ''));
        if ($content === '') {
            return ['success' => false, 'error' => 'empty_reply'];
        }

        // 7. Xarajat — OpenRouter real cost bo'lsa uni ishlatamiz
        $costUzs = $this->computeCost($model, $result);

        // 8. Billing + agent hisoblagichlari (bitta tranzaksiya ichida)
        if ($costUzs > 0) {
            $this->deductFromWallet($owner->id, $wallet, $costUzs, $agent, $model);
        }
        $agent->recordSpend($costUzs);
        $agent->total_replies = (int) $agent->total_replies + 1;
        $agent->save();

        // 9. Assistant xabarini saqlash
        $this->storeMessage($conversation, $agent, 'assistant', $content, [
            'model_id'      => $model->model_id,
            'tokens_input'  => $result['tokens_input'] ?? 0,
            'tokens_output' => $result['tokens_output'] ?? 0,
            'cost_uzs'      => $costUzs,
            'latency_ms'    => $latencyMs,
        ]);

        $conversation->last_message_at = now();
        $conversation->save();

        return [
            'success'       => true,
            'content'       => $content,
            'cost_uzs'      => $costUzs,
            'tokens_input'  => $result['tokens_input'] ?? 0,
            'tokens_output' => $result['tokens_output'] ?? 0,
            'model'         => $model->model_id,
        ];
    }

    /** System + tarix + yangi user xabari. */
    protected function buildMessages(Agent $agent, AgentConversation $conversation, string $userText): array
    {
        $systemParts = [];
        if ($preset = self::PRESETS[$agent->behavior_preset] ?? null) {
            $systemParts[] = $preset;
        }
        if ($agent->system_prompt) {
            $systemParts[] = $agent->system_prompt;
        }
        $systemParts[] = "Your name is \"{$agent->name}\".";

        $messages = [[
            'role'    => 'system',
            'content' => implode("\n\n", $systemParts),
        ]];

        // Oxirgi N xabar (yangi user xabaridan oldingi tarix)
        $limit = max(2, (int) $agent->memory_limit);
        $history = $conversation->messages()
            ->whereIn('role', ['user', 'assistant'])
            ->orderByDesc('id')
            ->limit($limit)
            ->get()
            ->reverse();

        foreach ($history as $m) {
            $messages[] = ['role' => $m->role, 'content' => (string) $m->content];
        }

        $messages[] = ['role' => 'user', 'content' => $userText];
        return $messages;
    }

    protected function storeMessage(AgentConversation $conversation, Agent $agent, string $role, string $content, array $extra = []): AgentMessage
    {
        $msg = AgentMessage::create(array_merge([
            'conversation_id' => $conversation->id,
            'agent_id'        => $agent->id,
            'role'            => $role,
            'content'         => $content,
        ], $extra));

        $conversation->increment('total_messages');
        $agent->increment('total_messages');

        return $msg;
    }

    /** Non-streaming model chaqiruvi (OpenRouter/Groq, OpenAI-compat). */
    protected function callModel(AiModel $model, array $messages, float $temperature, ?int $maxTokens): array
    {
        try {
            $provider = $model->provider ?? 'openrouter';
            [$url, $headers] = $this->getProviderConfig($provider);

            $payload = [
                'model'       => $model->model_id,
                'messages'    => $messages,
                'temperature' => $temperature,
                'stream'      => false,
            ];
            if ($maxTokens) $payload['max_tokens'] = $maxTokens;
            // OpenRouter'dan real xarajatni so'rash
            if ($provider !== 'groq') {
                $payload['usage'] = ['include' => true];
            }

            $response = Http::withHeaders($headers)->timeout(120)->post($url, $payload);

            if (!$response->successful()) {
                $errorData = $response->json() ?? [];
                return ['success' => false, 'error' => $errorData['error']['message'] ?? "HTTP {$response->status()}"];
            }

            $data = $response->json();
            $choice = $data['choices'][0] ?? null;
            if (!$choice) {
                return ['success' => false, 'error' => 'no_choice'];
            }

            return [
                'success'       => true,
                'content'       => $choice['message']['content'] ?? '',
                'finish_reason' => $choice['finish_reason'] ?? 'stop',
                'tokens_input'  => $data['usage']['prompt_tokens'] ?? 0,
                'tokens_output' => $data['usage']['completion_tokens'] ?? 0,
                'upstream_cost' => (float) ($data['usage']['cost'] ?? 0),
            ];
        } catch (\Throwable $e) {
            Log::error('AgentRunner callModel exception', ['error' => $e->getMessage()]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    protected function getProviderConfig(string $provider): array
    {
        if ($provider === 'groq') {
            return ['https://api.groq.com/openai/v1/chat/completions', [
                'Authorization' => 'Bearer ' . env('GROQ_API_KEY'),
                'Content-Type'  => 'application/json',
            ]];
        }

        return ['https://openrouter.ai/api/v1/chat/completions', [
            'Authorization' => 'Bearer ' . env('OPENROUTER_API_KEY'),
            'Content-Type'  => 'application/json',
            'HTTP-Referer'  => config('app.url', 'https://cloudapi.uz'),
            'X-Title'       => 'CloudAPI Agents',
        ]];
    }

    /** Xarajat — upstream real cost ustuvor, aks holda tokenlardan. */
    protected function computeCost(AiModel $model, array $result): float
    {
        $upstream = (float) ($result['upstream_cost'] ?? 0);
        if ($upstream > 0) {
            $rate = $model->usd_to_uzs ?: 12700;
            $marginPct = ($model->margin_percent && $model->margin_percent > 0) ? $model->margin_percent : 30;
            return round($upstream * $rate * (1 + $marginPct / 100), 2);
        }
        if ($model->is_free) return 0.0;
        return $model->calculateCost((int) ($result['tokens_input'] ?? 0), (int) ($result['tokens_output'] ?? 0));
    }

    /** Egasining hamyonidan yechish (bonus avval) + tranzaksiya yozuvi. */
    protected function deductFromWallet(int $userId, Wallet $wallet, float $amount, Agent $agent, AiModel $model): void
    {
        DB::transaction(function () use ($userId, $wallet, $amount, $agent, $model) {
            $wallet = Wallet::where('id', $wallet->id)->lockForUpdate()->first();
            $remaining = $amount;

            if ($wallet->bonus_balance_uzs > 0) {
                $fromBonus = min((float) $wallet->bonus_balance_uzs, $remaining);
                $wallet->decrement('bonus_balance_uzs', $fromBonus);
                $remaining -= $fromBonus;
            }
            if ($remaining > 0) {
                $wallet->decrement('balance_uzs', $remaining);
            }

            $wallet->refresh();
            $newBalance = (float) ($wallet->balance_uzs ?? 0) + (float) ($wallet->bonus_balance_uzs ?? 0);

            Transaction::create([
                'user_id'       => $userId,
                'wallet_id'     => $wallet->id,
                'type'          => 'usage',
                'status'        => 'completed',
                'amount_uzs'    => -$amount,
                'balance_after' => $newBalance,
                'description'   => "Agent: {$agent->name} · {$model->display_name}",
                'meta'          => [
                    'source'    => 'agent',
                    'agent_id'  => $agent->id,
                    'model_id'  => $model->model_id,
                ],
            ]);
        });
    }
}
