<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\AiModel;
use App\Models\ProxyUsage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ImagesController extends Controller
{
    /**
     * POST /v1/images/generations
     * OpenAI-compatible image generation
     */
    public function generate(Request $request)
    {
        $proxyKey = $request->attributes->get('proxy_key');
        $user = $proxyKey->user;
        $wallet = $user->wallet;

        $validated = $request->validate([
            'model' => 'required|string',
            'prompt' => 'required|string|max:4000',
            'n' => 'nullable|integer|min:1|max:10',
            'size' => 'nullable|string',
            'quality' => 'nullable|string',
            'response_format' => 'nullable|in:url,b64_json',
            'style' => 'nullable|string',
        ]);

        $model = AiModel::where('model_id', $validated['model'])->where('active', true)->first();
        if (!$model) {
            return response()->json([
                'error' => ['message' => "Model not found", 'type' => 'invalid_request_error']
            ], 404);
        }

        // Image models — narx odatda har rasm uchun fiksirovangan
        if (!$model->is_free) {
            $estimatedCost = ($validated['n'] ?? 1) * 1000; // 1000 so'm per image (rough)
            if (!$wallet || $wallet->balance_uzs < $estimatedCost) {
                return response()->json([
                    'error' => ['message' => 'Insufficient balance', 'type' => 'insufficient_balance']
                ], 402);
            }
        }

        $startTime = microtime(true);

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . env('OPENROUTER_API_KEY'),
            'HTTP-Referer' => env('OPENROUTER_REFERER', 'https://cloudapi.uz'),
            'Content-Type' => 'application/json',
        ])->timeout(180)->post(env('OPENROUTER_BASE_URL') . '/images/generations', $validated);

        $latency = (int)((microtime(true) - $startTime) * 1000);

        if (!$response->successful()) {
            return response()->json($response->json(), $response->status());
        }

        $data = $response->json();
        $n = $validated['n'] ?? 1;

        // Cost: oddiy rasm uchun 1000 so'm × marja
        $costUzs = 0;
        if (!$model->is_free && $wallet) {
            $costUzs = $n * 1000 * (1 + $model->margin_percent / 100);
            $wallet->withdraw($costUzs, 'usage', "Image: {$model->display_name} (n={$n})");
        }

        ProxyUsage::create([
            'proxy_key_id' => $proxyKey->id,
            'user_id' => $user->id,
            'model' => $model->model_id,
            'provider' => 'openrouter',
            'tokens_in' => 0,
            'tokens_out' => 0,
            'cost_usd' => $costUzs / 12700,
            'cost_uzs' => $costUzs,
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