<?php

namespace App\Services\Video;

use App\Models\AiModel;

/**
 * Video modelni provayderiga qarab yo'naltiradi (fal / replicate).
 */
class VideoGenerationService
{
    public function providerFor(AiModel $model): ?VideoProvider
    {
        return match ($model->provider) {
            'fal' => app(FalVideoProvider::class),
            'replicate' => app(ReplicateVideoProvider::class),
            default => null,
        };
    }

    /**
     * @param  callable  $onProgress  fn(string $statusText): void
     * @return array{success:bool, video_url?:string, cost_usd?:float, error?:string}
     */
    public function generate(AiModel $model, string $prompt, callable $onProgress): array
    {
        $provider = $this->providerFor($model);
        if (!$provider) {
            return ['success' => false, 'error' => "Video provayder '{$model->provider}' qo'llab-quvvatlanmaydi"];
        }
        if (!$provider->isConfigured()) {
            $label = strtoupper($model->provider);
            return ['success' => false, 'error' => "{$label} API kaliti sozlanmagan (.env)"];
        }
        return $provider->generate($model, $prompt, [], $onProgress);
    }
}
