<?php

namespace App\Services\Video;

use App\Models\AiModel;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * fal.ai video generatsiya (queue API). Text-to-video va image-to-video.
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
        $modelPath = $model->model_id;

        $input = array_merge(['prompt' => $prompt], $meta['input'] ?? []);

        // Rasm-dan-video: rasm bo'lsa image_url qo'shamiz va (bo'lsa) image endpoint'ga o'tamiz
        if (!empty($options['image_url'])) {
            $input['image_url'] = $options['image_url'];
            if (!empty($meta['image_model_id'])) {
                $modelPath = $meta['image_model_id'];
            }
        }

        try {
            $submit = Http::withHeaders([
                'Authorization' => "Key {$key}",
                'Content-Type' => 'application/json',
            ])->timeout(60)->post("{$base}/{$modelPath}", $input);

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

            $videoUrl = $this->findUrl($rj);
            if (!$videoUrl) {
                return ['success' => false, 'error' => 'fal: video URL topilmadi. Javob: ' . substr(json_encode($rj), 0, 300)];
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

    /**
     * Javob ichidan video URL'ni rekursiv qidiradi (turli struktura uchun mustahkam).
     */
    protected function findUrl($data): ?string
    {
        $urls = [];
        $walk = function ($node) use (&$walk, &$urls) {
            if (is_string($node) && str_starts_with($node, 'http')) {
                $urls[] = $node;
            } elseif (is_array($node)) {
                foreach ($node as $v) $walk($v);
            }
        };
        $walk($data);

        if (!$urls) return null;
        foreach ($urls as $u) {
            if (preg_match('#\.(mp4|webm|mov|m4v)(\?|$)#i', $u)) return $u;
        }
        return $urls[0];
    }
}
