<?php

namespace App\Services\Video;

use App\Models\AiModel;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Replicate video generatsiya (predictions API).
 * Model id = "owner/name", masalan "minimax/video-01".
 */
class ReplicateVideoProvider implements VideoProvider
{
    public function isConfigured(): bool
    {
        return !empty(config('services.replicate.key'));
    }

    public function generate(AiModel $model, string $prompt, array $options, callable $onProgress): array
    {
        $key = config('services.replicate.key');
        if (!$key) return ['success' => false, 'error' => 'REPLICATE_API_TOKEN sozlanmagan'];

        $base = rtrim(config('services.replicate.base_url', 'https://api.replicate.com/v1'), '/');
        $meta = $model->metadata ?? [];
        $input = array_merge(['prompt' => $prompt], $meta['input'] ?? [], $options);

        try {
            $submit = Http::withHeaders([
                'Authorization' => "Bearer {$key}",
                'Content-Type' => 'application/json',
            ])->timeout(60)->post("{$base}/models/{$model->model_id}/predictions", ['input' => $input]);

            if (!$submit->successful()) {
                return ['success' => false, 'error' => 'replicate submit: HTTP ' . $submit->status() . ' ' . substr($submit->body(), 0, 200)];
            }

            $data = $submit->json();
            $getUrl = $data['urls']['get'] ?? null;
            $status = $data['status'] ?? '';
            $output = $data['output'] ?? null;

            $deadline = time() + 600; // 10 daqiqa
            while (!in_array($status, ['succeeded', 'failed', 'canceled'], true) && time() < $deadline) {
                sleep(3);
                if (!$getUrl) break;
                $p = Http::withHeaders(['Authorization' => "Bearer {$key}"])->timeout(30)->get($getUrl);
                $pj = $p->json() ?? [];
                $status = $pj['status'] ?? '';
                $output = $pj['output'] ?? $output;
                $onProgress('Video yaratilmoqda… (' . $status . ')');
            }

            if ($status !== 'succeeded') {
                return ['success' => false, 'error' => "replicate: {$status}"];
            }

            $videoUrl = is_array($output) ? ($output[0] ?? null) : $output;
            if (!$videoUrl || !is_string($videoUrl)) {
                return ['success' => false, 'error' => 'replicate: output topilmadi'];
            }

            return [
                'success' => true,
                'video_url' => $videoUrl,
                'cost_usd' => (float) ($meta['price_usd'] ?? 0.5),
            ];
        } catch (\Throwable $e) {
            Log::error('replicate video failed', ['error' => $e->getMessage()]);
            return ['success' => false, 'error' => 'replicate: ' . $e->getMessage()];
        }
    }
}
