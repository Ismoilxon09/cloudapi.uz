<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\AiModel;
use App\Models\ProxyUsage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class EmbeddingsController extends Controller
{
    /**
     * POST /v1/embeddings
     * OpenAI-compatible embeddings endpoint
     */
    public function create(Request $request)
    {
        $proxyKey = $request->attributes->get('proxy_key');
        $user = $proxyKey->user;
        $wallet = $user->wallet;

        $validated = $request->validate([
            'model' => 'required|string',
            'input' => 'required',
            'encoding_format' => 'nullable|in:float,base64',
            'dimensions' => 'nullable|integer',
        ]);

        $model = AiModel::where('model_id', $validated['model'])->where('active', true)->first();
        if (!$model) {
            return response()->json([
                'error' => ['message' => "Model not found", 'type' => 'invalid_request_error']
            ], 404);
        }

        if (!$model->is_free && (!$wallet || $wallet->balance_uzs < 100)) {
            return response()->json([
                'error' => ['message' => 'Insufficient balance', 'type' => 'insufficient_balance']
            ], 402);
        }

        $startTime = microtime(true);

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . env('OPENROUTER_API_KEY'),
            'HTTP-Referer' => env('OPENROUTER_REFERER', 'https://cloudapi.uz'),
            'Content-Type' => 'application/json',
        ])->timeout(60)->post(env('OPENROUTER_BASE_URL') . '/embeddings', $validated);

        $latency = (int)((microtime(true) - $startTime) * 1000);

        if (!$response->successful()) {
            return response()->json($response->json(), $response->status());
        }

        $data = $response->json();
        $tokens = $data['usage']['total_tokens'] ?? 0;

        // Cost
        if (!$model->is_free && $wallet) {
            $costUzs = $tokens * $model->cost_input_usd / 1000000 * (1 + $model->margin_percent / 100) * $model->usd_to_uzs;
            if ($costUzs > 0) {
                $wallet->withdraw($costUzs, 'usage', "Embeddings: {$model->display_name} ({$tokens}t)");
            }
        }

        ProxyUsage::create([
            'proxy_key_id' => $proxyKey->id,
            'user_id' => $user->id,
            'model' => $model->model_id,
            'provider' => 'openrouter',
            'tokens_in' => $tokens,
            'tokens_out' => 0,
            'cost_usd' => $tokens * $model->cost_input_usd / 1000000,
            'cost_uzs' => $model->is_free ? 0 : ($costUzs ?? 0),
            'latency_ms' => $latency,
            'status_code' => 200,
            'ip' => $request->ip(),
            'user_agent' => substr($request->userAgent() ?? '', 0, 500),
            'created_at' => now(),
        ]);

        $proxyKey->increment('total_requests');
        $proxyKey->update(['last_used_at' => now()]);

        return response()->json($data);
    }
}