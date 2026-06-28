<?php

namespace App\Services;

use App\Models\AiModel;
use App\Services\Groq\GroqService;
use App\Services\OpenRouter\ChatService;
use Illuminate\Support\Facades\Log;

class ProviderRouter
{
    public function __construct(
        protected ChatService $openRouterService,
        protected GroqService $groqService
    ) {}

    /**
     * Modelni topish va to'g'ri provider'ga yuborish (oddiy so'rov)
     *
     * @return array ['response' => [...], 'model' => AiModel]
     * @throws \Exception
     */
    public function sendRequest(string $slug, array $body): array
    {
        // Modelni topish
        $models = AiModel::resolveAllBySlug($slug);

        if ($models->isEmpty()) {
            throw new \Exception("Model '{$slug}' topilmadi", 404);
        }

        $lastError = null;

        // Priority bo'yicha urinish (1 birinchi)
        foreach ($models as $model) {
            try {
                $response = $this->sendToProvider($model, $body);
                return [
                    'response' => $response,
                    'model' => $model,
                ];
            } catch (\Exception $e) {
                $lastError = $e->getMessage();
                Log::warning("Provider {$model->provider} failed for {$slug}: {$e->getMessage()}, trying next...");
                continue;
            }
        }

        throw new \Exception("Barcha provider'lar ishlamadi. Oxirgi xato: {$lastError}", 503);
    }

    /**
     * Streaming so'rov
     */
    public function streamRequest(string $slug, array $body, callable $onChunk): array
    {
        $models = AiModel::resolveAllBySlug($slug);

        if ($models->isEmpty()) {
            throw new \Exception("Model '{$slug}' topilmadi", 404);
        }

        $lastError = null;

        foreach ($models as $model) {
            try {
                $result = $this->streamToProvider($model, $body, $onChunk);
                return [
                    'result' => $result,
                    'model' => $model,
                ];
            } catch (\Exception $e) {
                $lastError = $e->getMessage();
                Log::warning("Stream provider {$model->provider} failed: {$e->getMessage()}");
                continue;
            }
        }

        throw new \Exception("Barcha provider'lar ishlamadi: {$lastError}", 503);
    }

    /**
     * Aniq providerga so'rov yuborish
     */
    protected function sendToProvider(AiModel $model, array $body): array
    {
        return match($model->provider) {
            'groq' => $this->groqService->sendRequest($model->model_id, $body),
            'openrouter' => $this->openRouterService->sendRequest($model->model_id, $body),
            default => throw new \Exception("Noma'lum provider: {$model->provider}"),
        };
    }

    /**
     * Aniq providerga stream yuborish
     */
    protected function streamToProvider(AiModel $model, array $body, callable $onChunk): array
    {
        return match($model->provider) {
            'groq' => $this->groqService->streamRequest($model->model_id, $body, $onChunk),
            'openrouter' => $this->openRouterService->streamRequest($model->model_id, $body, $onChunk),
            default => throw new \Exception("Noma'lum provider: {$model->provider}"),
        };
    }
}