<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\AiModel;
use App\Models\ProxyUsage;
use App\Services\ProviderRouter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ChatController extends Controller
{
    public function __construct(protected ProviderRouter $router) {}

    public function completions(Request $request)
    {
        $proxyKey = $request->attributes->get('proxy_key');
        $user = $proxyKey->user;
        $wallet = $user->wallet;

        $validated = $request->validate([
            'model' => 'required|string',
            'messages' => 'required|array|min:1',
            'temperature' => 'nullable|numeric|between:0,2',
            'max_tokens' => 'nullable|integer|min:1|max:32000',
            'stream' => 'nullable|boolean',
            'tools' => 'nullable|array',
            'tool_choice' => 'nullable',
            'response_format' => 'nullable|array',
            'top_p' => 'nullable|numeric',
            'frequency_penalty' => 'nullable|numeric',
            'presence_penalty' => 'nullable|numeric',
            'stop' => 'nullable',
            'seed' => 'nullable|integer',
        ]);

        $stream = (bool)($validated['stream'] ?? false);
        $modelSlug = $validated['model'];

        // Auto router
        if ($modelSlug === 'cloudapi/auto' || $modelSlug === 'openrouter/auto') {
            $autoRouter = app(\App\Services\OpenRouter\AutoRouterService::class);
            $selectedModel = $autoRouter->selectBestModel($validated['messages']);
            if ($selectedModel) {
                $modelSlug = $selectedModel->slug ?: $selectedModel->model_id;
            }
        }

        // Modelni topish (slug bo'yicha)
        $model = AiModel::resolveBySlug($modelSlug);
        if (!$model) {
            return response()->json([
                'error' => [
                    'message' => "Model '{$modelSlug}' topilmadi yoki faol emas",
                    'type' => 'invalid_request_error',
                    'code' => 'model_not_found',
                ],
            ], 404);
        }

        // Allowed models tekshiruvi (per-key)
        if ($proxyKey->allowed_models && !empty($proxyKey->allowed_models)) {
            $allowed = in_array($model->slug, $proxyKey->allowed_models) ||
                       in_array($model->model_id, $proxyKey->allowed_models);
            if (!$allowed) {
                return response()->json([
                    'error' => [
                        'message' => "Bu API kalit '{$modelSlug}' ga ruxsat etilmagan",
                        'type' => 'invalid_request_error',
                        'code' => 'model_forbidden',
                    ],
                ], 403);
            }
        }

        // Balans tekshiruvi (faqat pullik model'lar uchun)
        if (!$model->is_free) {
            if (!$wallet || $wallet->balance_uzs < 100) {
                return response()->json([
                    'error' => [
                        'message' => 'Balans yetarli emas. To\'ldirish: https://cloudapi.uz/billing',
                        'type' => 'insufficient_balance',
                        'code' => 'insufficient_balance',
                    ],
                ], 402);
            }
        }

        // So'rov body tayyorlash
        $body = collect($validated)
            ->except(['model', 'stream'])
            ->filter()
            ->toArray();

        $startTime = microtime(true);

        // === STREAMING ===
        if ($stream) {
            return $this->streamResponse($modelSlug, $body, $proxyKey, $user, $wallet, $request, $startTime);
        }

        // === ODDIY SO'ROV ===
        try {
            $result = $this->router->sendRequest($modelSlug, $body);
            $response = $result['response'];
            $actualModel = $result['model']; // qaysi provider ishlatilgan

            $latency = (int)((microtime(true) - $startTime) * 1000);
            $tokensIn = $response['usage']['prompt_tokens'] ?? 0;
            $tokensOut = $response['usage']['completion_tokens'] ?? 0;

            // Wallet'dan yechish
            if (!$actualModel->is_free) {
                $costUzs = $actualModel->calculateCost($tokensIn, $tokensOut);
                if ($costUzs > 0 && $wallet) {
                    $wallet->withdraw(
                        $costUzs,
                        'usage',
                        "{$actualModel->display_name} ({$actualModel->provider}, {$tokensIn}+{$tokensOut}t)"
                    );
                }
            }

            // Statistika
            ProxyUsage::create([
                'proxy_key_id' => $proxyKey->id,
                'user_id' => $user->id,
                'model' => $actualModel->model_id,
                'provider' => $actualModel->provider,
                'tokens_in' => $tokensIn,
                'tokens_out' => $tokensOut,
                'cost_usd' => $actualModel->is_free ? 0 : ($tokensIn * $actualModel->cost_input_usd / 1000000 + $tokensOut * $actualModel->cost_output_usd / 1000000),
                'cost_uzs' => $actualModel->is_free ? 0 : $actualModel->calculateCost($tokensIn, $tokensOut),
                'latency_ms' => $latency,
                'status_code' => 200,
                'ip' => $request->ip(),
                'user_agent' => substr($request->userAgent() ?? '', 0, 500),
                'created_at' => now(),
            ]);

            $proxyKey->increment('total_requests');
            $proxyKey->increment('total_tokens', $tokensIn + $tokensOut);
            $proxyKey->update(['last_used_at' => now()]);

            return response()->json($response);

        } catch (\Exception $e) {
            Log::error("Chat completion error: " . $e->getMessage());
            return response()->json([
                'error' => [
                    'message' => $e->getMessage(),
                    'type' => 'provider_error',
                    'code' => 'provider_error',
                ],
            ], $e->getCode() >= 400 && $e->getCode() < 600 ? $e->getCode() : 500);
        }
    }

    /**
     * Streaming response
     */
    protected function streamResponse(string $modelSlug, $body, $proxyKey, $user, $wallet, $request, $startTime): StreamedResponse
    {
        return new StreamedResponse(function () use ($modelSlug, $body, $proxyKey, $user, $wallet, $request, $startTime) {
            if (ob_get_level()) ob_end_clean();
            ini_set('output_buffering', 'off');
            ini_set('zlib.output_compression', false);

            try {
                $result = $this->router->streamRequest(
                    $modelSlug,
                    $body,
                    function ($chunk) {
                        echo $chunk;
                        if (ob_get_level()) ob_flush();
                        flush();
                    }
                );

                $actualModel = $result['model'];
                $streamResult = $result['result'];

                $latency = (int)((microtime(true) - $startTime) * 1000);
                $tokensIn = $streamResult['usage']['prompt_tokens'] ?? 0;
                $tokensOut = $streamResult['usage']['completion_tokens'] ?? 0;

                if (!$actualModel->is_free && $wallet) {
                    $costUzs = $actualModel->calculateCost($tokensIn, $tokensOut);
                    if ($costUzs > 0) {
                        $wallet->withdraw($costUzs, 'usage', "{$actualModel->display_name} stream ({$actualModel->provider}, {$tokensIn}+{$tokensOut}t)");
                    }
                }

                ProxyUsage::create([
                    'proxy_key_id' => $proxyKey->id,
                    'user_id' => $user->id,
                    'model' => $actualModel->model_id,
                    'provider' => $actualModel->provider,
                    'tokens_in' => $tokensIn,
                    'tokens_out' => $tokensOut,
                    'cost_usd' => $actualModel->is_free ? 0 : ($tokensIn * $actualModel->cost_input_usd / 1000000 + $tokensOut * $actualModel->cost_output_usd / 1000000),
                    'cost_uzs' => $actualModel->is_free ? 0 : $actualModel->calculateCost($tokensIn, $tokensOut),
                    'latency_ms' => $latency,
                    'status_code' => 200,
                    'ip' => $request->ip(),
                    'user_agent' => substr($request->userAgent() ?? '', 0, 500),
                    'created_at' => now(),
                ]);

                $proxyKey->increment('total_requests');
                $proxyKey->increment('total_tokens', $tokensIn + $tokensOut);
                $proxyKey->update(['last_used_at' => now()]);

            } catch (\Exception $e) {
                Log::error('Stream error: ' . $e->getMessage());
                echo "data: " . json_encode(['error' => ['message' => $e->getMessage()]]) . "\n\n";
                flush();
            }
        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache, no-transform',
            'X-Accel-Buffering' => 'no',
            'Connection' => 'keep-alive',
        ]);
    }
}