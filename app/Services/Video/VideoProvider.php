<?php

namespace App\Services\Video;

use App\Models\AiModel;

/**
 * Video generatsiya provayderi uchun umumiy interfeys (fal.ai / Replicate).
 */
interface VideoProvider
{
    /**
     * Video yaratadi (asinxron: job yuborish → poll → URL).
     *
     * @param  callable  $onProgress  fn(string $statusText): void
     * @return array{success:bool, video_url?:string, cost_usd?:float, error?:string}
     */
    public function generate(AiModel $model, string $prompt, array $options, callable $onProgress): array;

    /** API kaliti sozlanganmi? */
    public function isConfigured(): bool;
}
