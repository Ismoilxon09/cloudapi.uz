<?php

namespace App\Console\Commands;

use App\Models\AiModel;
use App\Services\Groq\GroqService;
use Illuminate\Console\Command;

class GroqSyncCommand extends Command
{
    protected $signature = 'groq:sync';
    protected $description = 'Groq dan barcha modellarni import qilish';

    public function handle(GroqService $groq): int
    {
        if (!$groq->isConfigured()) {
            $this->error('GROQ_API_KEY .env da o\'rnatilmagan!');
            return 1;
        }

        $this->info('Groq modellarini olish...');

        try {
            $models = $groq->listModels();
        } catch (\Exception $e) {
            $this->error('Xato: ' . $e->getMessage());
            return 1;
        }

        if (empty($models)) {
            $this->warn('Hech qanday model topilmadi');
            return 0;
        }

        $this->info("Topildi: " . count($models) . " ta model");
        $this->newLine();

        $displayNames = [
            'llama-3.3-70b-versatile' => 'Llama 3.3 70B',
            'llama-3.1-70b-versatile' => 'Llama 3.1 70B',
            'llama-3.1-8b-instant' => 'Llama 3.1 8B Instant',
            'llama3-70b-8192' => 'Llama 3 70B',
            'llama3-8b-8192' => 'Llama 3 8B',
            'mixtral-8x7b-32768' => 'Mixtral 8x7B',
            'gemma2-9b-it' => 'Gemma 2 9B',
            'gemma-7b-it' => 'Gemma 7B',
            'llama-3.2-1b-preview' => 'Llama 3.2 1B',
            'llama-3.2-3b-preview' => 'Llama 3.2 3B',
            'llama-3.2-11b-vision-preview' => 'Llama 3.2 11B Vision',
            'llama-3.2-90b-vision-preview' => 'Llama 3.2 90B Vision',
            'llama-3.2-11b-text-preview' => 'Llama 3.2 11B Text',
            'llama-3.2-90b-text-preview' => 'Llama 3.2 90B Text',
            'llama-3.3-70b-specdec' => 'Llama 3.3 70B SpecDec',
            'deepseek-r1-distill-llama-70b' => 'DeepSeek R1 Distill 70B',
            'qwen-qwq-32b' => 'Qwen QwQ 32B',
            'qwen-2.5-32b' => 'Qwen 2.5 32B',
            'qwen-2.5-coder-32b' => 'Qwen 2.5 Coder 32B',
            'mistral-saba-24b' => 'Mistral Saba 24B',
            'allam-2-7b' => 'ALLaM 2 7B',
            'whisper-large-v3' => 'Whisper Large V3',
            'whisper-large-v3-turbo' => 'Whisper V3 Turbo',
            'distil-whisper-large-v3-en' => 'Distil Whisper V3 EN',
            'llama-guard-3-8b' => 'Llama Guard 3 8B',
            'meta-llama/llama-guard-4-12b' => 'Llama Guard 4 12B',
        ];

        $slugMap = [
            'llama-3.3-70b-versatile' => 'llama-3.3-70b',
            'llama-3.1-70b-versatile' => 'llama-3.1-70b',
            'llama-3.1-8b-instant' => 'llama-3.1-8b',
            'llama3-70b-8192' => 'llama-3-70b',
            'llama3-8b-8192' => 'llama-3-8b',
            'mixtral-8x7b-32768' => 'mixtral-8x7b',
            'gemma2-9b-it' => 'gemma-2-9b',
            'gemma-7b-it' => 'gemma-7b',
            'llama-3.2-1b-preview' => 'llama-3.2-1b',
            'llama-3.2-3b-preview' => 'llama-3.2-3b',
            'llama-3.2-11b-vision-preview' => 'llama-3.2-11b-vision',
            'llama-3.2-90b-vision-preview' => 'llama-3.2-90b-vision',
            'llama-3.2-11b-text-preview' => 'llama-3.2-11b',
            'llama-3.2-90b-text-preview' => 'llama-3.2-90b',
            'llama-3.3-70b-specdec' => 'llama-3.3-70b-specdec',
            'deepseek-r1-distill-llama-70b' => 'deepseek-r1-distill',
            'qwen-qwq-32b' => 'qwen-qwq-32b',
            'qwen-2.5-32b' => 'qwen-2.5-32b',
            'qwen-2.5-coder-32b' => 'qwen-2.5-coder',
            'mistral-saba-24b' => 'mistral-saba',
            'allam-2-7b' => 'allam-2-7b',
            'whisper-large-v3' => 'whisper-v3',
            'whisper-large-v3-turbo' => 'whisper-v3-turbo',
            'distil-whisper-large-v3-en' => 'distil-whisper-v3',
            'llama-guard-3-8b' => 'llama-guard-3',
            'meta-llama/llama-guard-4-12b' => 'llama-guard-4',
        ];

        $featured = [
            'llama-3.3-70b',
            'llama-3.1-70b',
            'mixtral-8x7b',
            'deepseek-r1-distill',
            'llama-3.2-90b-vision',
            'qwen-2.5-coder',
            'whisper-v3-turbo',
        ];

        $created = 0;
        $updated = 0;

        foreach ($models as $m) {
            $modelId = $m['id'] ?? null;
            if (!$modelId) continue;

            $slug = $slugMap[$modelId] ?? $this->generateSlug($modelId);
            $displayName = $displayNames[$modelId] ?? $this->humanizeName($modelId);

            $category = 'chat';
            $supportsVision = false;
            $supportsTools = false;
            $description = '';

            if (str_contains($modelId, 'whisper') || str_contains($modelId, 'distil')) {
                $category = 'audio';
                $description = "Audio'dan matnga - Groq orqali ultra-tez";
            } elseif (str_contains($modelId, 'guard')) {
                $category = 'moderation';
                $description = "Kontent moderatsiyasi (xavfsizlik)";
            } elseif (str_contains($modelId, 'vision')) {
                $category = 'vision';
                $supportsVision = true;
                $description = "Vision model - rasmlarni tahlil qila oladi";
            } elseif (str_contains($modelId, 'coder')) {
                $category = 'coding';
                $supportsTools = true;
                $description = "Coding uchun maxsus model";
            } elseif (str_contains($modelId, 'deepseek-r1') || str_contains($modelId, 'qwq')) {
                $category = 'reasoning';
                $supportsTools = true;
                $description = "Reasoning model - mantiqiy fikrlash";
            } else {
                $supportsTools = str_contains($modelId, 'llama') ||
                                  str_contains($modelId, 'mixtral') ||
                                  str_contains($modelId, 'qwen');
                $description = "Universal chat model";
            }

            $existing = AiModel::where('model_id', $modelId)
                ->where('provider', 'groq')
                ->first();

            $data = [
                'model_id' => $modelId,
                'slug' => $slug,
                'display_name' => $displayName,
                'provider' => 'groq',
                'priority' => 1,
                'category' => $category,
                'context_length' => $m['context_window'] ?? 8192,
                'max_output_tokens' => $m['max_completion_tokens'] ?? 4096,
                'cost_input_usd' => 0,
                'cost_output_usd' => 0,
                'is_free' => true,
                'is_featured' => in_array($slug, $featured),
                'supports_streaming' => $category !== 'audio',
                'supports_tools' => $supportsTools,
                'supports_vision' => $supportsVision,
                'active' => true,
                'usd_to_uzs' => 12700,
                'margin_percent' => 0,
                'description' => $description,
                'sort_order' => in_array($slug, $featured) ? 1 : 10,
            ];

            if ($existing) {
                $existing->update($data);
                $updated++;
                $this->line("  ↻ {$displayName} <fg=gray>[{$category}]</>");
            } else {
                AiModel::create($data);
                $created++;
                $this->line("  <fg=green>✓</> {$displayName} <fg=gray>[{$category}]</>");
            }
        }

        $this->newLine();
        $this->info("OpenRouter ekvivalentlariga fallback priority...");
        $fallbackCount = 0;
        foreach ($slugMap as $groqId => $slug) {
            $orModels = AiModel::where('slug', $slug)
                ->where('provider', 'openrouter')
                ->get();
            foreach ($orModels as $or) {
                $or->update(['priority' => 2]);
                $fallbackCount++;
            }
        }
        $this->line("  {$fallbackCount} ta OpenRouter model priority=2 ga o'tkazildi");

        $this->newLine();
        $this->info("=== NATIJA ===");
        $this->line("<fg=green>Yangi:</> {$created}");
        $this->line("<fg=yellow>Yangilandi:</> {$updated}");
        $this->newLine();
        $this->info("Groq modellari ishga tushdi! /playground'da sinab ko'ring.");

        return 0;
    }

    protected function humanizeName(string $id): string
    {
        $clean = str_replace(['meta-llama/', '/'], '', $id);
        $name = preg_replace('/[-_]/', ' ', $clean);
        $name = preg_replace('/\s+/', ' ', $name);
        return ucwords(trim($name));
    }

    protected function generateSlug(string $modelId): string
    {
        $slug = str_replace(['meta-llama/', '/'], '', $modelId);
        $slug = preg_replace('/-preview$/', '', $slug);
        $slug = preg_replace('/-\d{4,}$/', '', $slug);
        return $slug;
    }
}