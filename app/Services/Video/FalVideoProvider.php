<?php

namespace App\Services\Video;

use App\Models\AiModel;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * fal.ai video generatsiya (queue API).
 * Model id = fal model yo'li, masalan "fal-ai/minimax/video-01".
 */
class FalVideoProvider implements VideoProvider
{
    public function isConfigured(): bool
    {
        return !empty(config('services.fal.key'));
    }

    public function generate(AiModel $model, string $prompt, array $options, callable $onProgress): array
    {
        $key = config('services.fal.key');
        if (!$key) return ['success' => false, 'error' => 'FAL_KEY sozlanmagan'];

        $base = rtrim(config('services.fal.base_url', 'https://queue.fal.run'), '/');
        $meta = $model->metadata ?? [];
        $input = array_merge(['prompt' => $prompt], $meta['input'] ?? [], $options);

        try {
            $submit = Http::withHeaders([
                'Authorization' => "Key {$key}",
                'Content-Type' => 'application/json',
            ])->timeout(60)->post("{$base}/{$model->model_id}", $input);

            if (!$submit->successful()) {
                return ['success' => false, 'error' => 'fal submit: HTTP ' . $submit->status() . ' ' . substr($submit->body(), 0, 200)];
            }

            $data = $submit->json();
            $statusUrl = $data['status_url'] ?? null;
            $responseUrl = $data['response_url'] ?? null;
            if (!$statusUrl || !$responseUrl) {
                return ['success' => false, 'error' => 'fal: status_url/response_url topilmadi'];
            }

            $deadline = time() + 600; // 10 daqiqa
            $status = 'IN_QUEUE';
            while (time() < $deadline) {
                sleep(3);
                $st = Http::withHeaders(['Authorization' => "Key {$key}"])->timeout(30)->get($statusUrl);
                $status = $st->json('status') ?? '';
                $onProgress(match ($status) {
                    'IN_QUEUE' => 'Navbatda…',
                    'IN_PROGRESS' => 'Video yaratilmoqda…',
                    default => $status ?: 'Kutilyapti…',
                });
                if ($status === 'COMPLETED') break;
                if (in_array($status, ['FAILED', 'ERROR', 'CANCELLED'], true)) {
                    return ['success' => false, 'error' => "fal: {$status}"];
                }
            }
            if ($status !== 'COMPLETED') {
                return ['success' => false, 'error' => 'fal: vaqt tugadi (timeout)'];
            }

            $res = Http::withHeaders(['Authorization' => "Key {$key}"])->timeout(60)->get($responseUrl);
            $rj = $res->json() ?? [];
            $videoUrl = $rj['video']['url']
                ?? ($rj['videos'][0]['url'] ?? null)
                ?? ($rj['output']['url'] ?? null)
                ?? (is_string($rj['output'] ?? null) ? $rj['output'] : null);
            if (!$videoUrl) {
                return ['success' => false, 'error' => 'fal: video URL topilmadi'];
            }

            return [
                'success' => true,
                'video_url' => $videoUrl,
                'cost_usd' => (float) ($meta['price_usd'] ?? 0.5),
            ];
        } catch (\Throwable $e) {
            Log::error('fal video failed', ['error' => $e->getMessage()]);
            return ['success' => false, 'error' => 'fal: ' . $e->getMessage()];
        }
    }
}
