<?php

namespace App\Services\OpenRouter;

use App\Models\AiModel;

class AutoRouterService
{
    /**
     * Savol turiga qarab eng yaxshi modelni tanlash
     */
    public function selectBestModel(array $messages, ?string $hint = null): ?AiModel
    {
        // Oxirgi user xabarini olish
        $lastMessage = collect($messages)->last(fn($m) => ($m['role'] ?? '') === 'user');
        $content = $lastMessage['content'] ?? '';

        // Agar content array bo'lsa (vision) — Vision modelga
        if (is_array($content)) {
            return $this->pickModel('vision', 'fast');
        }

        $text = strtolower((string)$content);
        $textLength = mb_strlen($text);

        // Coding so'rovlar
        if ($this->isCoding($text)) {
            return $this->pickModel('code', 'quality');
        }

        // Matematika va reasoning
        if ($this->needsReasoning($text)) {
            return $this->pickModel('reasoning', 'quality');
        }

        // Uzun text (>1000 belgi) — kuchli modelga
        if ($textLength > 1000) {
            return $this->pickModel('chat', 'quality');
        }

        // Hint berilgan bo'lsa
        if ($hint) {
            return match($hint) {
                'fast', 'cheap' => $this->pickModel('chat', 'fast'),
                'quality', 'best' => $this->pickModel('chat', 'quality'),
                default => $this->pickModel('chat', 'balanced'),
            };
        }

        // Default: balansli
        return $this->pickModel('chat', 'balanced');
    }

    /**
     * Coding tilini aniqlash
     */
    protected function isCoding(string $text): bool
    {
        $keywords = ['code', 'function', 'class', 'array', 'variable', 'kod', 'функция', 'класс',
                     'python', 'javascript', 'php', 'laravel', 'react', 'sql', 'api', 'debug',
                     'error', 'xato', 'ошибка', '```'];

        foreach ($keywords as $kw) {
            if (str_contains($text, $kw)) return true;
        }

        // Code-like patterns
        if (preg_match('/[\{\}\;\(\)\=\>]{3,}/', $text)) return true;

        return false;
    }

    /**
     * Reasoning kerakmi?
     */
    protected function needsReasoning(string $text): bool
    {
        $keywords = ['hisobla', 'isbot', 'qadam', 'logic', 'step by step', 'reasoning',
                     'математ', 'докажи', 'посчитай', 'логик', 'why', 'how',
                     'nima uchun', 'qanday', 'почему', 'как'];

        foreach ($keywords as $kw) {
            if (str_contains($text, $kw)) return true;
        }

        return false;
    }

    /**
     * Kategoriya va sifat darajasiga qarab model tanlash
     */
    protected function pickModel(string $category, string $tier): ?AiModel
    {
        $preferences = match($tier) {
            'fast' => [
                'chat' => ['openai/gpt-4o-mini', 'google/gemini-2.0-flash-exp', 'anthropic/claude-3-haiku'],
                'code' => ['openai/gpt-4o-mini', 'anthropic/claude-3-haiku'],
                'vision' => ['openai/gpt-4o-mini', 'google/gemini-2.0-flash-exp'],
            ],
            'quality' => [
                'chat' => ['anthropic/claude-3.5-sonnet', 'openai/gpt-4o', 'google/gemini-pro-1.5'],
                'code' => ['anthropic/claude-3.5-sonnet', 'openai/gpt-4o', 'deepseek/deepseek-coder'],
                'reasoning' => ['openai/o1-preview', 'anthropic/claude-3.5-sonnet', 'deepseek/deepseek-r1'],
                'vision' => ['openai/gpt-4o', 'anthropic/claude-3.5-sonnet', 'google/gemini-pro-1.5'],
            ],
            default => [ // balanced
                'chat' => ['anthropic/claude-3.5-sonnet', 'openai/gpt-4o-mini', 'google/gemini-flash-1.5'],
                'code' => ['anthropic/claude-3.5-sonnet', 'openai/gpt-4o-mini'],
                'reasoning' => ['anthropic/claude-3.5-sonnet', 'openai/gpt-4o'],
                'vision' => ['openai/gpt-4o-mini', 'anthropic/claude-3.5-sonnet'],
            ],
        };

        $candidates = $preferences[$category] ?? $preferences['chat'] ?? [];

        // DB dan birinchi mavjudini topish
        foreach ($candidates as $modelId) {
            $model = AiModel::where('model_id', $modelId)->where('active', true)->first();
            if ($model) return $model;
        }

        // Fallback: kategoriya bo'yicha eng arzon featured
        return AiModel::where('category', $category)
            ->where('active', true)
            ->where('is_featured', true)
            ->orderBy('cost_input_usd')
            ->first()
            ?? AiModel::where('active', true)->where('is_featured', true)->first();
    }
}