<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class Agent extends Model
{
    protected $fillable = [
        'user_id', 'name', 'slug', 'avatar', 'description',
        'system_prompt', 'behavior_preset', 'greeting',
        'model_mode', 'model_slug', 'model_pool', 'temperature', 'max_tokens', 'memory_limit',
        'status', 'is_public',
        'spend_cap_daily_uzs', 'daily_spend_uzs', 'daily_spend_date', 'total_spent_uzs',
        'total_messages', 'total_replies', 'last_active_at',
    ];

    protected $casts = [
        'model_pool'          => 'array',
        'temperature'         => 'float',
        'max_tokens'          => 'integer',
        'memory_limit'        => 'integer',
        'is_public'           => 'boolean',
        'spend_cap_daily_uzs' => 'decimal:2',
        'daily_spend_uzs'     => 'decimal:2',
        'daily_spend_date'    => 'date',
        'total_spent_uzs'     => 'decimal:2',
        'last_active_at'      => 'datetime',
    ];

    // === Relations ===
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function channels(): HasMany { return $this->hasMany(AgentChannel::class); }
    public function conversations(): HasMany { return $this->hasMany(AgentConversation::class); }
    public function messages(): HasMany { return $this->hasMany(AgentMessage::class); }

    public function telegramChannel(): HasOne
    {
        return $this->hasOne(AgentChannel::class)->where('type', 'telegram');
    }

    // === Scopes ===
    public function scopeActive($q) { return $q->where('status', 'active'); }

    // === Model resolver ===
    /**
     * Agent qaysi modelni ishlatishi kerak — mode bo'yicha.
     * pool/any bo'lsa birinchi mavjud modelni tanlaydi (fallback keyin kengaytiriladi).
     */
    public function resolveModel(): ?AiModel
    {
        if ($this->model_mode === 'single' && $this->model_slug) {
            return AiModel::resolveBySlug($this->model_slug);
        }

        if ($this->model_mode === 'pool' && is_array($this->model_pool)) {
            foreach ($this->model_pool as $slug) {
                if ($m = AiModel::resolveBySlug($slug)) return $m;
            }
        }

        // any — arzon, ishonchli standart
        if ($this->model_slug && $m = AiModel::resolveBySlug($this->model_slug)) {
            return $m;
        }
        return AiModel::resolveBySlug('gpt-4o-mini')
            ?? AiModel::active()->orderBy('priority')->first();
    }

    // === Spend cap ===
    public function refreshDailyWindow(): void
    {
        $today = Carbon::today()->toDateString();
        if ($this->daily_spend_date?->toDateString() !== $today) {
            $this->daily_spend_date = $today;
            $this->daily_spend_uzs = 0;
        }
    }

    public function isOverDailyCap(): bool
    {
        if ($this->spend_cap_daily_uzs === null) return false;
        $this->refreshDailyWindow();
        return (float)$this->daily_spend_uzs >= (float)$this->spend_cap_daily_uzs;
    }

    /**
     * Sarfni yozib qo'yish (kunlik oyna + umumiy).
     */
    public function recordSpend(float $uzs): void
    {
        $this->refreshDailyWindow();
        $this->daily_spend_uzs = (float)$this->daily_spend_uzs + $uzs;
        $this->total_spent_uzs = (float)$this->total_spent_uzs + $uzs;
        $this->last_active_at  = now();
    }

    public static function generateSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'agent';
        $slug = $base;
        $i = 1;
        while (self::where('slug', $slug)->exists()) {
            $slug = $base . '-' . (++$i);
        }
        return $slug;
    }
}
