<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;

class AgentChannel extends Model
{
    protected $fillable = [
        'agent_id', 'type', 'status', 'external_id', 'webhook_secret', 'config', 'connected_at',
    ];

    protected $casts = [
        'config'       => 'array',
        'connected_at' => 'datetime',
    ];

    public function agent(): BelongsTo { return $this->belongsTo(Agent::class); }

    public function isActive(): bool { return $this->status === 'active'; }

    // === Telegram token (config ichida shifrlangan holda) ===
    public function setTelegramToken(string $token): void
    {
        $config = $this->config ?? [];
        $config['bot_token'] = Crypt::encryptString($token);
        $this->config = $config;
    }

    public function getTelegramToken(): ?string
    {
        $enc = $this->config['bot_token'] ?? null;
        if (!$enc) return null;
        try {
            return Crypt::decryptString($enc);
        } catch (\Throwable $e) {
            return null;
        }
    }

    public static function newWebhookSecret(): string
    {
        return Str::random(48);
    }

    // === API kalit ('api' kanal uchun; hash saqlanadi, to'liq kalit faqat bir marta ko'rsatiladi) ===
    public static function newApiKey(): string
    {
        return 'agtk_' . Str::random(40);
    }

    public function setApiKey(string $key): void
    {
        $config = $this->config ?? [];
        $config['api_key_hash']   = hash('sha256', $key);
        $config['api_key_prefix'] = substr($key, 0, 12);
        $this->config = $config;
    }

    public function matchesApiKey(string $key): bool
    {
        $hash = $this->config['api_key_hash'] ?? null;
        return $hash !== null && hash_equals($hash, hash('sha256', $key));
    }
}
