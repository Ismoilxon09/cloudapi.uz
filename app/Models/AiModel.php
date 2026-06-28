<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AiModel extends Model
{
    protected $table = 'ai_models';

    protected $fillable = [
        'model_id',
        'slug',
        'display_name',
        'provider',
        'priority',
        'category',
        'description',
        'context_length',
        'max_output_tokens',
        'cost_input_usd',
        'cost_output_usd',
        'cost_image_usd',
        'margin_percent',
        'usd_to_uzs',
        'is_free',
        'is_featured',
        'supports_vision',
        'supports_tools',
        'supports_streaming',
        'active',
        'sort_order',
        'metadata',
    ];

    protected $casts = [
        'cost_input_usd' => 'float',
        'cost_output_usd' => 'float',
        'cost_image_usd' => 'float',
        'margin_percent' => 'float',
        'usd_to_uzs' => 'float',
        'priority' => 'integer',
        'is_free' => 'boolean',
        'is_featured' => 'boolean',
        'supports_vision' => 'boolean',
        'supports_tools' => 'boolean',
        'supports_streaming' => 'boolean',
        'active' => 'boolean',
        'metadata' => 'array',
    ];

    /**
     * SMART RESOLVER — har xil formatdagi model nomini topadi
     *
     * Misollar (hammasi bir xil natija beradi):
     *   - "llama-3.1-70b"                              (slug)
     *   - "meta-llama/llama-3.1-70b-instruct"          (to'liq model_id)
     *   - "meta-llama/llama-3.1-70b-instruct:free"     (free variant)
     *   - "llama-3.1-70b-versatile"                    (Groq id)
     */
    public static function resolveBySlug(string $identifier): ?self
    {
        // Sanitize — bo'sh joylarni olib tashlash
        $identifier = trim($identifier);

        // === 1. Avval AYNAN model_id bo'yicha topish (eng aniq match) ===
        $model = self::where('model_id', $identifier)
            ->where('active', true)
            ->first();
        if ($model) return $model;

        // === 2. Slug bo'yicha (multi-provider routing) ===
        $model = self::where('slug', $identifier)
            ->where('active', true)
            ->orderBy('priority', 'asc')
            ->first();
        if ($model) return $model;

        // === 3. ":free" qo'shimchasini olib tashlab, slug bilan urinish ===
        $cleanIdentifier = str_replace(':free', '', $identifier);
        if ($cleanIdentifier !== $identifier) {
            $model = self::where('slug', $cleanIdentifier)
                ->where('active', true)
                ->orderBy('priority', 'asc')
                ->first();
            if ($model) return $model;

            // ":free" siz aynan model_id
            $model = self::where('model_id', $cleanIdentifier)
                ->where('active', true)
                ->first();
            if ($model) return $model;
        }

        // === 4. Oxirgi qism bo'yicha (provider/name dan name) ===
        if (str_contains($identifier, '/')) {
            $lastPart = substr($identifier, strrpos($identifier, '/') + 1);
            $lastPart = str_replace(':free', '', $lastPart);

            $model = self::where('slug', $lastPart)
                ->where('active', true)
                ->orderBy('priority', 'asc')
                ->first();
            if ($model) return $model;
        }

        // === 5. LIKE bilan eng yaqin variant ===
        $model = self::where('active', true)
            ->where(function($q) use ($identifier, $cleanIdentifier) {
                $q->where('model_id', 'LIKE', "%{$identifier}%")
                  ->orWhere('model_id', 'LIKE', "%{$cleanIdentifier}%")
                  ->orWhere('slug', 'LIKE', "%{$cleanIdentifier}%");
            })
            ->orderBy('priority', 'asc')
            ->first();

        return $model;
    }

    /**
     * Slug bo'yicha BARCHA provider'larni olish (fallback uchun)
     */
    public static function resolveAllBySlug(string $identifier): \Illuminate\Database\Eloquent\Collection
    {
        // Slug topish uchun avval bitta yozuv topib, uning slug'i bilan barchasini olamiz
        $primary = self::resolveBySlug($identifier);
        if (!$primary) {
            return new \Illuminate\Database\Eloquent\Collection();
        }

        return self::where('slug', $primary->slug)
            ->where('active', true)
            ->orderBy('priority', 'asc')
            ->get();
    }

    /**
     * Tokenlar uchun narx hisoblash
     */
    public function calculateCost(int $tokensIn, int $tokensOut): float
    {
        if ($this->is_free) return 0.0;
        if ($this->cost_input_usd <= 0 && $this->cost_output_usd <= 0) return 0.0;

        $usdRate = $this->usd_to_uzs ?: 12700;
        $margin = ($this->margin_percent ?: 30) / 100;

        $inputCostUsd = ($tokensIn / 1000000) * $this->cost_input_usd;
        $outputCostUsd = ($tokensOut / 1000000) * $this->cost_output_usd;

        $totalUsd = $inputCostUsd + $outputCostUsd;
        $totalUzs = $totalUsd * $usdRate * (1 + $margin);

        return round($totalUzs, 4);
    }

    public function getInputPriceUzs(): float
    {
        if ($this->is_free || $this->cost_input_usd <= 0) return 0.0;
        $usdRate = $this->usd_to_uzs ?: 12700;
        $margin = ($this->margin_percent ?: 30) / 100;
        return round($this->cost_input_usd * $usdRate * (1 + $margin), 2);
    }

    public function getOutputPriceUzs(): float
    {
        if ($this->is_free || $this->cost_output_usd <= 0) return 0.0;
        $usdRate = $this->usd_to_uzs ?: 12700;
        $margin = ($this->margin_percent ?: 30) / 100;
        return round($this->cost_output_usd * $usdRate * (1 + $margin), 2);
    }

    public function getFinalPriceInput(): float
    {
        if ($this->is_free || $this->cost_input_usd <= 0) return 0.0;
        $margin = ($this->margin_percent ?: 30) / 100;
        return $this->cost_input_usd * (1 + $margin);
    }

    public function getFinalPriceOutput(): float
    {
        if ($this->is_free || $this->cost_output_usd <= 0) return 0.0;
        $margin = ($this->margin_percent ?: 30) / 100;
        return $this->cost_output_usd * (1 + $margin);
    }

    /**
     * Provider tezligi/badge ko'rsatish uchun
     */
    public function getProviderBadge(): array
    {
        return match($this->provider) {
            'groq' => ['label' => 'Groq', 'icon' => '⚡', 'color' => '#F55036', 'speed' => '280 t/s'],
            'openrouter' => ['label' => 'OpenRouter', 'icon' => '🌐', 'color' => '#3B82F6', 'speed' => null],
            default => ['label' => $this->provider, 'icon' => '🤖', 'color' => '#6B7280', 'speed' => null],
        };
    }

    public function scopeActive($q) { return $q->where('active', true); }
    public function scopeFeatured($q) { return $q->where('is_featured', true); }
    public function scopeFree($q) { return $q->where('is_free', true); }
    public function scopeGroq($q) { return $q->where('provider', 'groq'); }
    public function scopeOpenrouter($q) { return $q->where('provider', 'openrouter'); }
}