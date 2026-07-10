<?php

namespace App\Services\Video;

use App\Models\AiModel;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Replicate video generatsiya (predictions API). Text- va image-to-video.
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
        $input = array_merge(['prompt' => $prompt], $meta['input'] ?? []);

        // Rasm-dan-video (kalit modelga qarab har xil bo'lishi mumkin)
        if (!empty($options['image_url'])) {
            $imageKey = $meta['image_key'] ?? 'first_frame_image';
            $input[$imageKey] = $options['image_url'];
        }

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

            $deadline = time() + 600;
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

            $videoUrl = $this->findUrl($output) ?? $this->findUrl($data['output'] ?? null);
            if (!$videoUrl) {
                return ['success' => false, 'error' => 'replicate: video URL topilmadi'];
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

    protected function findUrl($data): ?string
    {
        if (is_string($data)) {
            return str_starts_with($data, 'http') ? $data : null;
        }
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
