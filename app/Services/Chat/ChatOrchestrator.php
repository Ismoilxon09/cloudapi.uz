<?php

namespace App\Services\Chat;

use App\Models\AiModel;
use App\Models\ChatAttachment;
use App\Models\ChatMessage;
use App\Models\ChatSession;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * ChatOrchestrator — chat xabarlarni model'ga yuborish,
 * cost hisoblash, wallet'dan yechish.
 */
class ChatOrchestrator
{
    /**
     * Non-streaming xabar yuborish (oddiy)
     */
    public function sendMessage(
        ChatSession $session,
        string $userContent,
        string $modelId,
        ?float $temperature = null,
        ?int $maxTokens = null,
        array $images = []
    ): array {
        $prep = $this->prepareRequest($session, $userContent, $modelId, $images);
        if (isset($prep['error'])) throw new \Exception($prep['error']);

        [$user, $model, $wallet, $history] = [$prep['user'], $prep['model'], $prep['wallet'], $prep['history']];

        // Non-streaming call
        $response = $this->callModel(
            $model,
            $history,
            $temperature ?? $session->temperature ?? 0.7,
            $maxTokens ?? $session->max_tokens,
            false
        );

        if (!$response['success']) {
            $assistantMessage = ChatMessage::create([
                'session_id' => $session->id,
                'user_id' => $user->id,
                'role' => 'assistant',
                'content' => "Kechirasiz, xatolik yuz berdi: " . $response['error'],
                'model_id' => $modelId,
                'error' => $response['error'],
            ]);
            return ['message' => $assistantMessage, 'error' => $response['error']];
        }

        // Cost hisoblash
        $tokensIn = $response['tokens_input'] ?? 0;
        $tokensOut = $response['tokens_output'] ?? 0;
        $costUzs = $this->calculateCost($model, $tokensIn, $tokensOut);

        if ($costUzs > 0) {
            $this->deductFromWallet($user, $wallet, $costUzs, $session, $model);
        }

        // Assistant xabarni saqlash
        $assistantMessage = ChatMessage::create([
            'session_id' => $session->id,
            'user_id' => $user->id,
            'role' => 'assistant',
            'content' => $response['content'],
            'model_id' => $modelId,
            'tokens_input' => $tokensIn,
            'tokens_output' => $tokensOut,
            'cost_uzs' => $costUzs,
            'finish_reason' => $response['finish_reason'] ?? 'stop',
        ]);

        $session->recordMessage($assistantMessage);

        return [
            'message' => $assistantMessage,
            'cost_uzs' => $costUzs,
            'tokens_input' => $tokensIn,
            'tokens_output' => $tokensOut,
        ];
    }

    /**
     * STREAMING xabar yuborish — real-time SSE
     * Callback bilan har chunk kelganda invoke qilinadi
     */
    public function streamMessage(
        ChatSession $session,
        string $userContent,
        string $modelId,
        callable $onChunk,
        ?float $temperature = null,
        ?int $maxTokens = null,
        array $images = []
    ): array {
        $wasNew = (int) ($session->total_messages ?? 0) === 0;

        $prep = $this->prepareRequest($session, $userContent, $modelId, $images);
        if (isset($prep['error'])) {
            $onChunk(['type' => 'error', 'error' => $prep['error']]);
            return ['error' => $prep['error']];
        }

        [$user, $model, $wallet, $history] = [$prep['user'], $prep['model'], $prep['wallet'], $prep['history']];

        // User xabarni frontend'ga yuborish (id + created_at bilan)
        $onChunk([
            'type' => 'user_message',
            'message' => [
                'id' => $prep['user_message']->id,
                'created_at' => $prep['user_message']->created_at->toIso8601String(),
            ],
            'session_id' => $session->id,
            'session_title' => $session->title,
        ]);

        if ($model->category === 'video') {
            $imageUri = $this->firstImageDataUri($prep['user_message']);
            $result = $this->streamVideo($session, $user, $model, $wallet, $userContent, $onChunk, $imageUri);
        } else {
            $result = $this->streamCompletion(
                $session, $user, $model, $wallet, $history, $onChunk,
                $temperature ?? $session->temperature ?? 0.7,
                $maxTokens
            );
        }

        // Birinchi xabardan keyin aqlli sarlavha yaratish
        if ($wasNew && trim($userContent) !== '') {
            $title = $this->generateSmartTitle($session, $userContent);
            if ($title) {
                $onChunk(['type' => 'title', 'title' => $title, 'session_id' => $session->id]);
            }
        }

        return $result;
    }

    /**
     * Birinchi xabardan arzon bepul model bilan qisqa sarlavha yaratish.
     */
    protected function generateSmartTitle(ChatSession $session, string $firstMessage): ?string
    {
        $firstMessage = trim($firstMessage);
        if ($firstMessage === '') return null;

        // Ishonchli arzon modelni afzal ko'ramiz (ba'zi bepul modellar o'lik bo'ladi)
        $titleModel = null;
        foreach (['openai/gpt-4o-mini', 'google/gemini-2.5-flash', 'meta-llama/llama-3.3-70b-instruct'] as $pid) {
            $titleModel = AiModel::where('active', 1)->where('model_id', $pid)->first();
            if ($titleModel) break;
        }
        if (!$titleModel) {
            $titleModel = AiModel::where('active', 1)->where('is_free', 0)
                ->where('category', 'chat')->orderBy('cost_output_usd')->first();
        }
        if (!$titleModel) return null;

        $prompt = "Quyidagi foydalanuvchi xabari uchun juda qisqa sarlavha yoz "
            . "(3-5 so'z, xabar tilida, tirnoqsiz va nuqtasiz, faqat sarlavhaning o'zi):\n\n"
            . mb_substr($firstMessage, 0, 500);

        try {
            $res = $this->callModel($titleModel, [['role' => 'user', 'content' => $prompt]], 0.3, 24, false);
        } catch (\Throwable $e) {
            return null;
        }
        if (!($res['success'] ?? false)) return null;

        $title = trim($res['content'] ?? '');
        $title = trim($title, " \t\n\r\0\x0B\"'`");
        $title = preg_replace('/\s+/', ' ', $title);
        $title = mb_substr($title, 0, 60);
        if ($title === '') return null;

        $session->update(['title' => $title]);
        return $title;
    }

    /**
     * Modelni oqim orqali chaqirib javobni stream qilish + saqlash + cost yechish.
     * streamMessage / streamRegenerate / streamEditResend uchun umumiy yadro.
     */
    protected function streamCompletion(
        ChatSession $session,
        User $user,
        AiModel $model,
        Wallet $wallet,
        array $history,
        callable $onChunk,
        ?float $temperature = null,
        ?int $maxTokens = null
    ): array {
        try {
            $provider = $model->provider ?? 'openrouter';
            $payload = [
                'model' => $model->model_id,
                'messages' => $history,
                'temperature' => $temperature ?? $session->temperature ?? 0.7,
                'stream' => true,
            ];
            if ($maxTokens) $payload['max_tokens'] = $maxTokens;

            // Generatsiya modellari uchun output modality kerak
            if ($model->category === 'image') {
                $payload['modalities'] = ['image', 'text'];
            } elseif ($model->category === 'audio') {
                $payload['modalities'] = ['audio', 'text'];
                // OpenAI gpt-audio streaming'da faqat pcm16 formatni qo'llaydi
                if (str_contains($model->model_id, 'gpt-audio')) {
                    $payload['audio'] = ['voice' => 'alloy', 'format' => 'pcm16'];
                }
            }

            [$url, $headers] = $this->getProviderConfig($provider);

            $fullContent = '';
            $tokensIn = 0;
            $tokensOut = 0;
            $finishReason = 'stop';
            $upstreamCostUsd = 0.0;
            $outputImages = [];
            $audioData = '';
            $audioFormat = null;

            $response = Http::withHeaders($headers)
                ->timeout(180)
                ->withOptions([
                    'stream' => true,
                    'read_timeout' => 180,
                ])
                ->post($url, $payload);

            if (!$response->successful()) {
                $errorData = $response->json() ?? [];
                $errorMsg = $errorData['error']['message'] ?? "HTTP {$response->status()}";
                $onChunk(['type' => 'error', 'error' => $errorMsg]);
                return ['error' => $errorMsg];
            }

            $body = $response->toPsrResponse()->getBody();
            $buffer = '';

            while (!$body->eof()) {
                $chunk = $body->read(8192);
                $buffer .= $chunk;

                while (($lineEnd = strpos($buffer, "\n")) !== false) {
                    $line = substr($buffer, 0, $lineEnd);
                    $buffer = substr($buffer, $lineEnd + 1);
                    $line = trim($line);

                    if (empty($line) || !str_starts_with($line, 'data: ')) continue;

                    $data = substr($line, 6);
                    if ($data === '[DONE]') break 2;

                    $json = json_decode($data, true);
                    if (!$json) continue;

                    // Delta content
                    $delta = $json['choices'][0]['delta']['content'] ?? '';
                    if ($delta !== '' && $delta !== null) {
                        $fullContent .= $delta;
                        $onChunk(['type' => 'delta', 'content' => $delta]);
                    }

                    // Delta images (rasm generatsiya)
                    $deltaImages = $json['choices'][0]['delta']['images'] ?? null;
                    if (is_array($deltaImages)) {
                        foreach ($deltaImages as $im) {
                            $u = $im['image_url']['url'] ?? ($im['url'] ?? null);
                            if ($u) {
                                $outputImages[] = $u;
                                $onChunk(['type' => 'image', 'url' => $u]);
                            }
                        }
                    }

                    // Delta audio (musiqa/audio generatsiya)
                    $deltaAudio = $json['choices'][0]['delta']['audio'] ?? null;
                    if (is_array($deltaAudio)) {
                        if (!empty($deltaAudio['data'])) $audioData .= $deltaAudio['data'];
                        if (!empty($deltaAudio['format'])) $audioFormat = $deltaAudio['format'];
                    }

                    // Usage (odatda so'nggi chunk'da) — cost bilan
                    if (isset($json['usage'])) {
                        $tokensIn = $json['usage']['prompt_tokens'] ?? 0;
                        $tokensOut = $json['usage']['completion_tokens'] ?? 0;
                        if (($json['usage']['cost'] ?? 0) > 0) {
                            $upstreamCostUsd = (float) $json['usage']['cost'];
                        }
                    }

                    if (isset($json['choices'][0]['finish_reason'])) {
                        $finishReason = $json['choices'][0]['finish_reason'];
                    }
                }
            }

            // Tokenlarni taxminiy hisoblash (agar usage kelmagan bo'lsa)
            if ($tokensOut === 0 && $fullContent) {
                $tokensOut = (int) ceil(mb_strlen($fullContent) / 4);
            }
            if ($tokensIn === 0) {
                $historyText = collect($history)->pluck('content')
                    ->map(fn($c) => is_array($c) ? '' : (string) $c)->implode(' ');
                $tokensIn = (int) ceil(mb_strlen($historyText) / 4);
            }

            // Cost — OpenRouter real xarajati bo'lsa uni ishlatamiz (rasm/audio uchun
            // aniq; "free" deb noto'g'ri belgilangan generatsiya modellarini ham qamraydi).
            if ($upstreamCostUsd > 0) {
                $rate = $model->usd_to_uzs ?: 12700;
                $marginPct = ($model->margin_percent && $model->margin_percent > 0) ? $model->margin_percent : 30;
                $costUzs = round($upstreamCostUsd * $rate * (1 + $marginPct / 100), 2);
            } elseif (!$model->is_free) {
                $costUzs = $this->calculateCost($model, $tokensIn, $tokensOut);
            } else {
                $costUzs = 0;
            }
            if ($costUzs > 0) {
                $this->deductFromWallet($user, $wallet, $costUzs, $session, $model);
            }

            // Assistant xabarini saqlash
            $assistantMessage = ChatMessage::create([
                'session_id' => $session->id,
                'user_id' => $user->id,
                'role' => 'assistant',
                'content' => $fullContent,
                'model_id' => $model->model_id,
                'tokens_input' => $tokensIn,
                'tokens_output' => $tokensOut,
                'cost_uzs' => $costUzs,
                'finish_reason' => $finishReason,
            ]);
            $session->recordMessage($assistantMessage);

            // Generatsiya qilingan rasmlarni saqlash (qayta yuklashda ko'rinishi uchun)
            foreach ($outputImages as $imgUrl) {
                $this->storeImageAttachment($assistantMessage, $user, ['data' => $imgUrl]);
            }

            // Generatsiya qilingan audioni saqlash + frontend'ga yuborish
            if ($audioData !== '') {
                $audioUrl = $this->storeAudioAttachment($assistantMessage, $user, $audioData, $audioFormat, $model->model_id);
                if ($audioUrl) $onChunk(['type' => 'audio', 'url' => $audioUrl]);
            }

            // Balance yangilanganini olish
            $wallet->refresh();
            $newBalance = ($wallet->balance_uzs ?? 0) + ($wallet->bonus_balance_uzs ?? 0);

            // Final chunk
            $onChunk([
                'type' => 'done',
                'message' => [
                    'id' => $assistantMessage->id,
                    'model_id' => $model->model_id,
                    'tokens_input' => $tokensIn,
                    'tokens_output' => $tokensOut,
                    'cost_uzs' => $costUzs,
                    'finish_reason' => $finishReason,
                ],
                'new_balance' => $newBalance,
            ]);

            return ['message' => $assistantMessage, 'cost_uzs' => $costUzs];
        } catch (\Exception $e) {
            Log::error("Stream failed", ['error' => $e->getMessage()]);
            $onChunk(['type' => 'error', 'error' => $e->getMessage()]);
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Oxirgi assistant javobini o'chirib, qayta yaratish (regenerate).
     */
    public function streamRegenerate(
        ChatSession $session,
        callable $onChunk,
        ?string $modelId = null,
        ?float $temperature = null,
        ?int $maxTokens = null
    ): array {
        $lastUser = $session->messages()->where('role', 'user')->orderByDesc('id')->first();
        if (!$lastUser) {
            $onChunk(['type' => 'error', 'error' => 'Qayta yaratish uchun xabar topilmadi']);
            return ['error' => 'no user message'];
        }

        $lastAssistant = $session->messages()->where('role', 'assistant')->orderByDesc('id')->first();
        $modelId = $modelId ?: ($lastAssistant->model_id ?? $session->model_id);
        if (!$modelId) {
            $onChunk(['type' => 'error', 'error' => 'Model aniqlanmadi']);
            return ['error' => 'no model'];
        }

        $ctx = $this->resolveContext($session, $modelId);
        if (isset($ctx['error'])) {
            $onChunk(['type' => 'error', 'error' => $ctx['error']]);
            return $ctx;
        }

        // Oxirgi assistant javobini o'chirish (agar u eng oxirgi xabar bo'lsa)
        if ($lastAssistant && $lastAssistant->id > $lastUser->id) {
            $this->rollbackCounters($session, $lastAssistant);
            $lastAssistant->delete();
        }

        $history = $this->buildHistory($session);

        return $this->streamCompletion(
            $session, $ctx['user'], $ctx['model'], $ctx['wallet'], $history, $onChunk,
            $temperature ?? $session->temperature, $maxTokens
        );
    }

    /**
     * User xabarni tahrirlab, undan keyingi hamma xabarlarni o'chirib qayta yuborish.
     */
    public function streamEditResend(
        ChatSession $session,
        int $messageId,
        string $newContent,
        callable $onChunk,
        ?string $modelId = null,
        ?float $temperature = null,
        ?int $maxTokens = null
    ): array {
        $target = $session->messages()->where('id', $messageId)->where('role', 'user')->first();
        if (!$target) {
            $onChunk(['type' => 'error', 'error' => 'Xabar topilmadi']);
            return ['error' => 'message not found'];
        }

        $modelId = $modelId ?: $session->model_id;
        if (!$modelId) {
            $lastAssistant = $session->messages()->where('role', 'assistant')->orderByDesc('id')->first();
            $modelId = $lastAssistant->model_id ?? null;
        }
        if (!$modelId) {
            $onChunk(['type' => 'error', 'error' => 'Model aniqlanmadi']);
            return ['error' => 'no model'];
        }

        $ctx = $this->resolveContext($session, $modelId);
        if (isset($ctx['error'])) {
            $onChunk(['type' => 'error', 'error' => $ctx['error']]);
            return $ctx;
        }

        // Matnni yangilash
        $target->update(['content' => $newContent]);

        // Ushbu xabardan keyingi barcha xabarlarni o'chirish
        $after = $session->messages()->where('id', '>', $target->id)->get();
        foreach ($after as $m) {
            $this->rollbackCounters($session, $m);
            foreach ($m->attachments as $att) {
                if ($att->path) Storage::disk('public')->delete($att->path);
                $att->delete();
            }
            $m->delete();
        }

        $history = $this->buildHistory($session);

        return $this->streamCompletion(
            $session, $ctx['user'], $ctx['model'], $ctx['wallet'], $history, $onChunk,
            $temperature ?? $session->temperature, $maxTokens
        );
    }

    /**
     * Model + wallet + balans tekshiruvi (yangi user xabar yaratmasdan).
     */
    protected function resolveContext(ChatSession $session, string $modelId): array
    {
        $user = $session->user;
        $model = AiModel::where('model_id', $modelId)->where('active', 1)->first();
        if (!$model) {
            return ['error' => "Model '{$modelId}' topilmadi yoki faol emas"];
        }

        $wallet = $user->wallet ?? Wallet::firstOrCreate(['user_id' => $user->id]);
        $available = ($wallet->balance_uzs ?? 0) + ($wallet->bonus_balance_uzs ?? 0);
        if ($available < 100 && !$model->is_free) {
            return ['error' => "Balans yetarli emas. Iltimos, hisobingizni to'ldiring."];
        }

        return ['user' => $user, 'model' => $model, 'wallet' => $wallet];
    }

    /**
     * Mavjud xabarlardan history qurish (oxirgi user xabarga rasmlarni ham qo'shib).
     */
    protected function buildHistory(ChatSession $session): array
    {
        $msgs = $session->messages()->with('attachments')->orderBy('created_at')->orderBy('id')->limit(20)->get();

        $history = [];
        foreach ($msgs as $m) {
            $history[] = ['role' => $m->role, 'content' => $m->content];
        }

        for ($i = count($history) - 1; $i >= 0; $i--) {
            if ($history[$i]['role'] === 'user') {
                $imgParts = $this->attachmentsToImageParts($msgs[$i]);
                if (!empty($imgParts)) {
                    $history[$i]['content'] = array_merge(
                        $msgs[$i]->content !== '' ? [['type' => 'text', 'text' => $msgs[$i]->content]] : [],
                        $imgParts
                    );
                }
                break;
            }
        }

        if ($session->system_prompt) {
            array_unshift($history, ['role' => 'system', 'content' => $session->system_prompt]);
        }

        return $history;
    }

    /**
     * Saqlangan rasm biriktirmalarni model uchun image_url part'larga aylantirish.
     */
    protected function attachmentsToImageParts(ChatMessage $message): array
    {
        $parts = [];
        foreach ($message->attachments as $att) {
            if (!str_starts_with($att->mime_type ?? '', 'image')) continue;
            if (!$att->path || !Storage::disk('public')->exists($att->path)) continue;
            try {
                $binary = Storage::disk('public')->get($att->path);
                if ($binary === null) continue;
                $b64 = base64_encode($binary);
                $parts[] = ['type' => 'image_url', 'image_url' => ['url' => "data:{$att->mime_type};base64,{$b64}"]];
            } catch (\Throwable $e) {
                Log::warning('attachment read failed', ['path' => $att->path]);
            }
        }
        return $parts;
    }

    /**
     * Xabarning birinchi rasm biriktirmasini data URI ko'rinishida qaytaradi (rasm-dan-video uchun).
     */
    protected function firstImageDataUri(ChatMessage $message): ?string
    {
        foreach ($message->attachments as $att) {
            if (!str_starts_with($att->mime_type ?? '', 'image')) continue;
            if (!$att->path || !Storage::disk('public')->exists($att->path)) continue;
            $bin = Storage::disk('public')->get($att->path);
            if ($bin !== null && $bin !== '') {
                return 'data:' . $att->mime_type . ';base64,' . base64_encode($bin);
            }
        }
        return null;
    }

    /**
     * O'chirilgan xabar uchun session hisoblagichlarini orqaga qaytarish.
     */
    protected function rollbackCounters(ChatSession $session, ChatMessage $m): void
    {
        if ($session->total_messages > 0) $session->decrement('total_messages');
        if ($m->tokens_input) $session->decrement('total_tokens_input', $m->tokens_input);
        if ($m->tokens_output) $session->decrement('total_tokens_output', $m->tokens_output);
        if ($m->cost_uzs) $session->decrement('total_cost_uzs', $m->cost_uzs);
    }

    /**
     * Umumiy tayyorlash (user message, history, wallet check)
     */
    protected function prepareRequest(ChatSession $session, string $userContent, string $modelId, array $images = []): array
    {
        $user = $session->user;
        $model = AiModel::where('model_id', $modelId)->where('active', 1)->first();

        if (!$model) {
            return ['error' => "Model '{$modelId}' topilmadi yoki faol emas"];
        }

        $wallet = $user->wallet ?? Wallet::firstOrCreate(['user_id' => $user->id]);
        $availableBalance = ($wallet->balance_uzs ?? 0) + ($wallet->bonus_balance_uzs ?? 0);

        if ($availableBalance < 100 && !$model->is_free) {
            return ['error' => "Balans yetarli emas. Iltimos, hisobingizni to'ldiring."];
        }

        // User xabar
        $userMessage = ChatMessage::create([
            'session_id' => $session->id,
            'user_id' => $user->id,
            'role' => 'user',
            'content' => $userContent,
        ]);

        // Rasm biriktirmalar (vision) — saqlash + model uchun data URL part
        $imageParts = [];
        foreach ($images as $img) {
            if (!is_array($img)) continue;
            $part = $this->storeImageAttachment($userMessage, $user, $img);
            if ($part) $imageParts[] = $part;
        }

        if ((int) ($session->total_messages ?? 0) === 0 && $session->title === 'Yangi chat') {
            $session->generateTitle($userContent !== '' ? $userContent : 'Rasm');
        }
        $session->recordMessage($userMessage);

        // History
        $history = $session->messages()
            ->orderBy('created_at')
            ->limit(20)
            ->get()
            ->map(fn($m) => ['role' => $m->role, 'content' => $m->content])
            ->toArray();

        // Oxirgi user xabarga rasmlarni multimodal qilib biriktirish
        if (!empty($imageParts)) {
            for ($i = count($history) - 1; $i >= 0; $i--) {
                if (($history[$i]['role'] ?? '') === 'user') {
                    $history[$i]['content'] = array_merge(
                        $userContent !== '' ? [['type' => 'text', 'text' => $userContent]] : [],
                        $imageParts
                    );
                    break;
                }
            }
        }

        if ($session->system_prompt) {
            array_unshift($history, ['role' => 'system', 'content' => $session->system_prompt]);
        }

        return [
            'user' => $user,
            'model' => $model,
            'wallet' => $wallet,
            'history' => $history,
            'user_message' => $userMessage,
        ];
    }

    /**
     * Base64 rasmni public diskka saqlash + model uchun image_url part qaytarish.
     * APP_URL ko'pincha localhost bo'lgani uchun modelga base64 data URL yuboriladi.
     */
    protected function storeImageAttachment(ChatMessage $message, User $user, array $img): ?array
    {
        $data = $img['data'] ?? '';
        if (!is_string($data) || $data === '') return null;

        $mime = $img['mime'] ?? 'image/png';
        $base64 = $data;
        if (preg_match('/^data:([^;]+);base64,(.*)$/s', $data, $m)) {
            $mime = $m[1];
            $base64 = $m[2];
        }

        $binary = base64_decode($base64, true);
        if ($binary === false) return null;

        if (strlen($binary) > 6 * 1024 * 1024) {
            Log::warning('Chat vision image too large, skipped', ['size' => strlen($binary)]);
            return null;
        }

        $ext = match ($mime) {
            'image/jpeg', 'image/jpg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            'image/gif' => 'gif',
            default => 'img',
        };

        $path = 'chat/' . $user->id . '/' . Str::uuid() . '.' . $ext;
        try {
            Storage::disk('public')->put($path, $binary);
            ChatAttachment::create([
                'message_id' => $message->id,
                'user_id' => $user->id,
                'type' => 'image',
                'filename' => basename($path),
                'original_name' => $img['name'] ?? basename($path),
                'mime_type' => $mime,
                'size_bytes' => strlen($binary),
                'path' => $path,
                'url' => '/storage/' . $path,
            ]);
        } catch (\Throwable $e) {
            Log::error('Chat attachment save failed', ['error' => $e->getMessage()]);
        }

        return [
            'type' => 'image_url',
            'image_url' => ['url' => "data:{$mime};base64,{$base64}"],
        ];
    }

    /**
     * Generatsiya qilingan audioni saqlash. gpt-audio pcm16 → WAV.
     * Qaytadi: brauzer uchun public URL (yoki null).
     */
    protected function storeAudioAttachment(ChatMessage $message, User $user, string $base64, ?string $format, string $modelId): ?string
    {
        $binary = base64_decode($base64, true);
        if ($binary === false || $binary === '') return null;

        if (str_contains($modelId, 'gpt-audio') || $format === 'pcm16') {
            $binary = $this->pcm16ToWav($binary, 24000, 1);
            $mime = 'audio/wav';
            $ext = 'wav';
        } else {
            [$mime, $ext] = $this->detectAudioMime($binary, $format);
        }

        if (strlen($binary) > 25 * 1024 * 1024) {
            Log::warning('Chat audio too large, skipped', ['size' => strlen($binary)]);
            return null;
        }

        $path = 'chat/' . $user->id . '/' . Str::uuid() . '.' . $ext;
        try {
            Storage::disk('public')->put($path, $binary);
            ChatAttachment::create([
                'message_id' => $message->id,
                'user_id' => $user->id,
                'type' => 'audio',
                'filename' => basename($path),
                'original_name' => 'audio.' . $ext,
                'mime_type' => $mime,
                'size_bytes' => strlen($binary),
                'path' => $path,
                'url' => '/storage/' . $path,
            ]);
        } catch (\Throwable $e) {
            Log::error('Chat audio save failed', ['error' => $e->getMessage()]);
            return null;
        }

        return '/storage/' . $path;
    }

    /**
     * Audio formatni magic bytes bo'yicha aniqlash (Lyria odatda WAV).
     */
    protected function detectAudioMime(string $binary, ?string $format): array
    {
        $head = substr($binary, 0, 4);
        if ($head === 'RIFF') return ['audio/wav', 'wav'];
        if ($head === 'OggS') return ['audio/ogg', 'ogg'];
        if ($head === 'fLaC') return ['audio/flac', 'flac'];
        if (str_starts_with($binary, 'ID3') || (strlen($binary) > 1 && ord($binary[0]) === 0xFF && (ord($binary[1]) & 0xE0) === 0xE0)) {
            return ['audio/mpeg', 'mp3'];
        }
        return match ($format) {
            'mp3' => ['audio/mpeg', 'mp3'],
            'opus' => ['audio/ogg', 'opus'],
            'flac' => ['audio/flac', 'flac'],
            default => ['audio/wav', 'wav'],
        };
    }

    /**
     * Xom PCM16 (24kHz mono) audioni WAV konteyneriga o'rash.
     */
    protected function pcm16ToWav(string $pcm, int $sampleRate = 24000, int $channels = 1): string
    {
        $bitsPerSample = 16;
        $byteRate = (int) ($sampleRate * $channels * $bitsPerSample / 8);
        $blockAlign = (int) ($channels * $bitsPerSample / 8);
        $dataLen = strlen($pcm);

        return 'RIFF' . pack('V', 36 + $dataLen) . 'WAVE'
            . 'fmt ' . pack('V', 16) . pack('v', 1) . pack('v', $channels)
            . pack('V', $sampleRate) . pack('V', $byteRate) . pack('v', $blockAlign) . pack('v', $bitsPerSample)
            . 'data' . pack('V', $dataLen) . $pcm;
    }

    /**
     * Video generatsiya oqimi (fal/replicate — asinxron, poll bilan).
     */
    protected function streamVideo(ChatSession $session, User $user, AiModel $model, Wallet $wallet, string $prompt, callable $onChunk, ?string $imageUrl = null): array
    {
        @set_time_limit(0);
        $onChunk(['type' => 'video_status', 'status' => $imageUrl ? 'Rasmdan video yaratilmoqda…' : 'Video yaratish boshlandi…']);

        $service = app(\App\Services\Video\VideoGenerationService::class);
        $result = $service->generate($model, $prompt, function ($status) use ($onChunk) {
            $onChunk(['type' => 'video_status', 'status' => $status]);
        }, $imageUrl);

        if (!($result['success'] ?? false)) {
            $err = $result['error'] ?? 'Video yaratishda xato';
            $onChunk(['type' => 'error', 'error' => $err]);
            $msg = ChatMessage::create([
                'session_id' => $session->id, 'user_id' => $user->id, 'role' => 'assistant',
                'content' => "Kechirasiz, video yaratilmadi: {$err}", 'model_id' => $model->model_id, 'error' => $err,
            ]);
            $session->recordMessage($msg);
            return ['error' => $err];
        }

        $assistantMessage = ChatMessage::create([
            'session_id' => $session->id, 'user_id' => $user->id, 'role' => 'assistant',
            'content' => '', 'model_id' => $model->model_id, 'finish_reason' => 'stop',
        ]);

        $videoUrl = $this->storeVideoFromUrl($assistantMessage, $user, $result['video_url']);

        $costUzs = 0;
        $costUsd = (float) ($result['cost_usd'] ?? 0);
        if ($costUsd > 0) {
            $rate = $model->usd_to_uzs ?: 12700;
            $marginPct = ($model->margin_percent && $model->margin_percent > 0) ? $model->margin_percent : 30;
            $costUzs = round($costUsd * $rate * (1 + $marginPct / 100), 2);
            $assistantMessage->update(['cost_uzs' => $costUzs]);
            if ($costUzs > 0) $this->deductFromWallet($user, $wallet, $costUzs, $session, $model);
        }
        $session->recordMessage($assistantMessage);

        $wallet->refresh();
        $newBalance = ($wallet->balance_uzs ?? 0) + ($wallet->bonus_balance_uzs ?? 0);

        if ($videoUrl) $onChunk(['type' => 'video', 'url' => $videoUrl]);
        $onChunk([
            'type' => 'done',
            'message' => [
                'id' => $assistantMessage->id, 'model_id' => $model->model_id,
                'tokens_input' => 0, 'tokens_output' => 0, 'cost_uzs' => $costUzs, 'finish_reason' => 'stop',
            ],
            'new_balance' => $newBalance,
        ]);

        return ['message' => $assistantMessage, 'cost_uzs' => $costUzs];
    }

    /**
     * Video URL'ni yuklab olib public diskka saqlash. Juda katta bo'lsa remote URL qoldiradi.
     */
    protected function storeVideoFromUrl(ChatMessage $message, User $user, string $url): ?string
    {
        try {
            $resp = Http::timeout(300)->get($url);
            if (!$resp->successful()) return $url;

            $binary = $resp->body();
            $mime = $resp->header('Content-Type') ?: 'video/mp4';
            $size = strlen($binary);

            // Haqiqiy video faylimi? (aks holda remote URL'ni qoldiramiz — u chaladi)
            $head = substr($binary, 0, 16);
            $looksVideo = str_starts_with($mime, 'video/')
                || str_contains($head, 'ftyp')                  // mp4/mov
                || str_starts_with($binary, "\x1A\x45\xDF\xA3"); // webm
            if ($size < 10000 || !$looksVideo || $size > 100 * 1024 * 1024) {
                Log::warning('video not stored (invalid/oversize), remote URL ishlatiladi', ['size' => $size, 'mime' => $mime]);
                return $url;
            }

            $ext = str_contains($mime, 'webm') ? 'webm' : 'mp4';
            $finalMime = str_starts_with($mime, 'video/') ? $mime : 'video/mp4';
            $path = 'chat/' . $user->id . '/' . Str::uuid() . '.' . $ext;
            Storage::disk('public')->put($path, $binary);
            ChatAttachment::create([
                'message_id' => $message->id, 'user_id' => $user->id, 'type' => 'video',
                'filename' => basename($path), 'original_name' => 'video.' . $ext,
                'mime_type' => $finalMime, 'size_bytes' => $size,
                'path' => $path, 'url' => '/storage/' . $path,
            ]);
            return '/storage/' . $path;
        } catch (\Throwable $e) {
            Log::error('video store failed', ['error' => $e->getMessage(), 'url' => $url]);
            return $url;
        }
    }

    /**
     * Non-streaming model call
     */
    protected function callModel(AiModel $model, array $messages, float $temperature, ?int $maxTokens, bool $stream = false): array
    {
        try {
            $provider = $model->provider ?? 'openrouter';
            [$url, $headers] = $this->getProviderConfig($provider);

            $payload = [
                'model' => $model->model_id,
                'messages' => $messages,
                'temperature' => $temperature,
                'stream' => $stream,
            ];
            if ($maxTokens) $payload['max_tokens'] = $maxTokens;

            $response = Http::withHeaders($headers)->timeout(120)->post($url, $payload);

            if (!$response->successful()) {
                $errorData = $response->json() ?? [];
                $errorMsg = $errorData['error']['message'] ?? "HTTP {$response->status()}";
                return ['success' => false, 'error' => $errorMsg];
            }

            $data = $response->json();
            $choice = $data['choices'][0] ?? null;
            if (!$choice) {
                return ['success' => false, 'error' => 'Model javob qaytarmadi'];
            }

            return [
                'success' => true,
                'content' => $choice['message']['content'] ?? '',
                'finish_reason' => $choice['finish_reason'] ?? 'stop',
                'tokens_input' => $data['usage']['prompt_tokens'] ?? 0,
                'tokens_output' => $data['usage']['completion_tokens'] ?? 0,
            ];
        } catch (\Exception $e) {
            Log::error("callModel failed", ['error' => $e->getMessage()]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Provider config (URL + headers)
     */
    protected function getProviderConfig(string $provider): array
    {
        if ($provider === 'groq') {
            $url = 'https://api.groq.com/openai/v1/chat/completions';
            $apiKey = env('GROQ_API_KEY');
            $headers = [
                'Authorization' => "Bearer {$apiKey}",
                'Content-Type' => 'application/json',
                'Accept' => 'text/event-stream',
            ];
        } else {
            $url = 'https://openrouter.ai/api/v1/chat/completions';
            $apiKey = env('OPENROUTER_API_KEY');
            $headers = [
                'Authorization' => "Bearer {$apiKey}",
                'Content-Type' => 'application/json',
                'Accept' => 'text/event-stream',
                'HTTP-Referer' => config('app.url', 'https://cloudapi.uz'),
                'X-Title' => 'CloudAPI Chat',
            ];
        }

        return [$url, $headers];
    }

    protected function calculateCost(AiModel $model, int $tokensIn, int $tokensOut): float
    {
        if ($model->is_free) return 0;

        $usdToUzs = $model->usd_to_uzs ?? 12700;
        $margin = ($model->margin_percent ?? 30) / 100;

        $costUsdIn = ($tokensIn / 1_000_000) * $model->cost_input_usd;
        $costUsdOut = ($tokensOut / 1_000_000) * $model->cost_output_usd;
        $costUsdTotal = $costUsdIn + $costUsdOut;

        $costUsdWithMargin = $costUsdTotal * (1 + $margin);
        $costUzs = $costUsdWithMargin * $usdToUzs;

        return round($costUzs, 2);
    }

    protected function deductFromWallet(User $user, Wallet $wallet, float $amount, ChatSession $session, AiModel $model): void
    {
        DB::transaction(function () use ($user, $wallet, $amount, $session, $model) {
            $wallet = Wallet::where('id', $wallet->id)->lockForUpdate()->first();
            $remainingAmount = $amount;

            if ($wallet->bonus_balance_uzs > 0) {
                $fromBonus = min($wallet->bonus_balance_uzs, $remainingAmount);
                $wallet->decrement('bonus_balance_uzs', $fromBonus);
                $remainingAmount -= $fromBonus;
            }

            if ($remainingAmount > 0) {
                $wallet->decrement('balance_uzs', $remainingAmount);
            }

            $wallet->refresh();
            $newBalance = ($wallet->balance_uzs ?? 0) + ($wallet->bonus_balance_uzs ?? 0);

            Transaction::create([
                'user_id' => $user->id,
                'wallet_id' => $wallet->id,
                'type' => 'usage',
                'status' => 'completed',
                'amount_uzs' => -$amount,
                'balance_after' => $newBalance,
                'description' => "Chat: {$model->display_name} · Session #{$session->id}",
                'meta' => [
                    'session_id' => $session->id,
                    'model_id' => $model->model_id,
                    'source' => 'chat',
                ],
            ]);
        });
    }
}