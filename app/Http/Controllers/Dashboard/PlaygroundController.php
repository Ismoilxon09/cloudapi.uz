<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\AiModel;
use App\Models\ProxyUsage;
use App\Services\ProviderRouter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PlaygroundController extends Controller
{
    public function __construct(protected ProviderRouter $router) {}

    public function index()
    {
        // Slug bo'yicha noyob modellar — dublikatsiz
        $models = AiModel::where('active', true)
            ->select('ai_models.*')
            ->whereIn('id', function($q) {
                $q->selectRaw('MIN(id)')
                    ->from('ai_models')
                    ->where('active', true)
                    ->groupBy('slug');
            })
            ->orderByDesc('is_featured')
            ->orderBy('sort_order')
            ->orderBy('display_name')
            ->get();

        return view('dashboard.playground.index', compact('models'));
    }

    public function run(Request $request)
    {
        try {
            $validated = $request->validate([
                'model' => 'required|string',
                'messages' => 'required|array|min:1',
                'temperature' => 'nullable|numeric|between:0,2',
                'max_tokens' => 'nullable|integer|min:1|max:8000',
                'stream' => 'nullable|boolean',
            ]);

            $user = Auth::user();
            $wallet = $user->wallet;
            $stream = (bool)($validated['stream'] ?? false);
            $modelSlug = $validated['model'];

            // Slug bo'yicha topish
            $model = AiModel::resolveBySlug($modelSlug);
            if (!$model) {
                return response()->json(['error' => "Model '{$modelSlug}' topilmadi"], 404);
            }

            if (!$model->is_free) {
                if (!$wallet || $wallet->balance_uzs < 100) {
                    return response()->json([
                        'error' => 'Balans yetarli emas. Iltimos, balansingizni to\'ldiring.',
                    ], 402);
                }
            }

            $body = [
                'messages' => $validated['messages'],
                'temperature' => $validated['temperature'] ?? 0.7,
                'max_tokens' => $validated['max_tokens'] ?? 1000,
            ];

            $startTime = microtime(true);

            // === STREAMING ===
            if ($stream) {
                return new StreamedResponse(function () use ($modelSlug, $body, $user, $wallet, $request, $startTime) {
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
                        $costUzs = $actualModel->is_free ? 0 : $actualModel->calculateCost($tokensIn, $tokensOut);

                        if ($costUzs > 0 && $wallet) {
                            $wallet->withdraw($costUzs, 'usage', "Playground: {$actualModel->display_name} ({$actualModel->provider}, stream)");
                        }

                        ProxyUsage::create([
                            'proxy_key_id' => null,
                            'user_id' => $user->id,
                            'model' => $actualModel->model_id,
                            'provider' => $actualModel->provider,
                            'tokens_in' => $tokensIn,
                            'tokens_out' => $tokensOut,
                            'cost_usd' => $costUzs / ($actualModel->usd_to_uzs ?: 12700),
                            'cost_uzs' => $costUzs,
                            'latency_ms' => $latency,
                            'status_code' => 200,
                            'ip' => $request->ip(),
                            'user_agent' => substr($request->userAgent() ?? '', 0, 500),
                            'created_at' => now(),
                        ]);

                        echo "data: " . json_encode([
                            'cost_uzs' => (float)$costUzs,
                            'provider' => $actualModel->provider,
                            'usage' => [
                                'prompt_tokens' => $tokensIn,
                                'completion_tokens' => $tokensOut,
                            ],
                        ]) . "\n\n";
                        flush();

                    } catch (\Exception $e) {
                        Log::error('Playground stream error: ' . $e->getMessage());
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

            // === ODDIY ===
            $result = $this->router->sendRequest($modelSlug, $body);
            $response = $result['response'];
            $actualModel = $result['model'];

            $latency = (int)((microtime(true) - $startTime) * 1000);
            $tokensIn = $response['usage']['prompt_tokens'] ?? 0;
            $tokensOut = $response['usage']['completion_tokens'] ?? 0;
            $costUzs = $actualModel->is_free ? 0 : $actualModel->calculateCost($tokensIn, $tokensOut);

            if ($costUzs > 0 && $wallet) {
                $wallet->withdraw($costUzs, 'usage', "Playground: {$actualModel->display_name} ({$actualModel->provider})");
            }

            ProxyUsage::create([
                'proxy_key_id' => null,
                'user_id' => $user->id,
                'model' => $actualModel->model_id,
                'provider' => $actualModel->provider,
                'tokens_in' => $tokensIn,
                'tokens_out' => $tokensOut,
                'cost_usd' => $costUzs / ($actualModel->usd_to_uzs ?: 12700),
                'cost_uzs' => $costUzs,
                'latency_ms' => $latency,
                'status_code' => 200,
                'ip' => $request->ip(),
                'user_agent' => substr($request->userAgent() ?? '', 0, 500),
                'created_at' => now(),
            ]);

            $response['cost_uzs'] = (float)$costUzs;
            $response['latency_ms'] = $latency;
            $response['provider'] = $actualModel->provider;
            $response['balance_after'] = $wallet ? (float)$wallet->fresh()->balance_uzs : 0;

            return response()->json($response);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['error' => 'Validation failed', 'details' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Playground error: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage() ?: 'Xato yuz berdi'], 500);
        }
    }
}