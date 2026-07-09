<?php

namespace App\Services\OpenRouter;

use App\Models\AiModel;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ModelSyncService
{
    protected ?string $apiKey;
    protected string $baseUrl;
    protected float $defaultMargin = 30; // 30% margin
    protected float $usdToUzs = 12700;

    public function __construct()
    {
        $this->apiKey = config('services.openrouter.key');
        $this->baseUrl = config('services.openrouter.base_url', 'https://openrouter.ai/api/v1');
    }

    /**
     * OpenRouter dan barcha modellarni sync qiladi
     */
    public function syncAll(): array
    {
        $stats = ['created' => 0, 'updated' => 0, 'skipped' => 0, 'errors' => 0];

        try {
            $response = Http::timeout(30)
                ->withHeaders($this->apiKey ? ['Authorization' => 'Bearer ' . $this->apiKey] : [])
                ->get($this->baseUrl . '/models');

            if (!$response->successful()) {
                throw new \Exception('OpenRouter API error: ' . $response->status());
            }

            $models = $response->json('data', []);

            foreach ($models as $modelData) {
                try {
                    $result = $this->syncModel($modelData);
                    $stats[$result]++;
                } catch (\Exception $e) {
                    Log::error('Model sync error: ' . $e->getMessage(), ['model' => $modelData['id'] ?? null]);
                    $stats['errors']++;
                }
            }

            return $stats;
        } catch (\Exception $e) {
            Log::error('OpenRouter sync failed: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Bitta modelni sync qiladi
     */
    protected function syncModel(array $data): string
    {
        $modelId = $data['id'] ?? null;
        if (!$modelId) return 'skipped';

        // Narxlar (OpenRouter $/token formatda yuboradi)
        $pricing = $data['pricing'] ?? [];
        $promptPrice = (float)($pricing['prompt'] ?? 0); // $/token
        $completionPrice = (float)($pricing['completion'] ?? 0);

        // $/1M tokens ga konvertatsiya
        $costInputUsd = $promptPrice * 1_000_000;
        $costOutputUsd = $completionPrice * 1_000_000;

        // Bepul modelni aniqlash
        $isFree = $costInputUsd <= 0 && $costOutputUsd <= 0;

        // Kategoriya
        $category = $this->detectCategory($data);

        // Capabilities
        $capabilities = $this->detectCapabilities($data);

        // Provider (modelId dan ajratish: "openai/gpt-4o" → "openai")
        $provider = explode('/', $modelId)[0] ?? 'unknown';

        // Featured: top providerlar
        $isFeatured = in_array($provider, ['openai', 'anthropic', 'google', 'meta-llama', 'deepseek']);

        // Margin: bepul modellar uchun yo'q, qolganlari uchun 30%
        $margin = $isFree ? 0 : $this->defaultMargin;

        $attributes = [
            'display_name'     => $data['name'] ?? $this->humanizeId($modelId),
            'provider'         => 'openrouter',
            'category'         => $category,
            'description'      => $data['description'] ?? null,
            'cost_input_usd'   => $costInputUsd,
            'cost_output_usd'  => $costOutputUsd,
            'margin_percent'   => $margin,
            'usd_to_uzs'       => $this->usdToUzs,
            'context_length'   => $data['context_length'] ?? null,
            'capabilities'     => $capabilities,
            'is_free'          => $isFree,
            'is_featured'      => $isFeatured,
            'active'           => true,
        ];

        $existing = AiModel::where('model_id', $modelId)->first();

        if ($existing) {
            $existing->update($attributes);
            return 'updated';
        }

        AiModel::create(array_merge(['model_id' => $modelId], $attributes));
        return 'created';
    }

    /**
     * Model kategoriyasini aniqlash
     */
    protected function detectCategory(array $data): string
    {
        $id = strtolower($data['id'] ?? '');
        $name = strtolower($data['name'] ?? '');
        $desc = strtolower($data['description'] ?? '');
        $combined = "$id $name $desc";

        $arch = $data['architecture'] ?? [];
        $output = $arch['output_modalities'] ?? [];
        if (!$output && !empty($arch['modality']) && str_contains($arch['modality'], '->')) {
            $output = explode('+', explode('->', $arch['modality'])[1]);
        }

        // Generatsiya modellari (chiqarish modaliteti) — vision'dan ustun
        if (in_array('image', $output, true)) return 'image';
        if (in_array('audio', $output, true)) return 'audio';
        if (in_array('video', $output, true)) return 'video';

        // Reasoning models
        if (preg_match('/\b(o1|o3|r1|reasoning|think)\b/', $combined)) {
            return 'reasoning';
        }

        // Vision/multimodal (kirish rasm)
        $inputModalities = $arch['input_modalities'] ?? [];
        $modality = $arch['modality'] ?? '';
        if (in_array('image', $inputModalities, true) || str_contains($modality, 'image') || str_contains($combined, 'vision')) {
            return 'vision';
        }

        // Code models
        if (preg_match('/\b(coder|code|codestral|deepseek-coder)\b/', $combined)) {
            return 'code';
        }

        // Embedding
        if (str_contains($combined, 'embed')) {
            return 'embedding';
        }

        return 'chat';
    }

    /**
     * Model imkoniyatlarini aniqlash
     */
    protected function detectCapabilities(array $data): array
    {
        $capabilities = [];

        $modality = $data['architecture']['modality'] ?? '';
        if (str_contains($modality, 'image')) {
            $capabilities[] = 'vision';
        }

        $supportedParams = $data['supported_parameters'] ?? [];
        if (in_array('tools', $supportedParams) || in_array('tool_choice', $supportedParams)) {
            $capabilities[] = 'tools';
        }
        if (in_array('response_format', $supportedParams)) {
            $capabilities[] = 'json_mode';
        }
        if (in_array('reasoning', $supportedParams)) {
            $capabilities[] = 'reasoning';
        }

        return $capabilities;
    }

    /**
     * "openai/gpt-4o" → "Gpt 4o"
     */
    protected function humanizeId(string $id): string
    {
        $parts = explode('/', $id);
        $name = end($parts);
        $name = str_replace(['-', '_'], ' ', $name);
        return ucwords($name);
    }
}