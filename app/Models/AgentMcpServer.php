<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Crypt;

class AgentMcpServer extends Model
{
    // 'headers' ataylab yo'q — faqat setHeaders() orqali shifrlab yoziladi
    protected $fillable = [
        'agent_id', 'name', 'url', 'transport',
        'enabled', 'status', 'tools', 'tools_count', 'last_error', 'last_checked_at',
    ];

    protected $casts = [
        'enabled'         => 'boolean',
        'tools'           => 'array',
        'tools_count'     => 'integer',
        'last_checked_at' => 'datetime',
    ];

    public function agent(): BelongsTo { return $this->belongsTo(Agent::class); }

    public function scopeEnabled($q) { return $q->where('enabled', true); }

    // === Headerlar (shifrlangan JSON) ===
    public function setHeaders(array $headers): void
    {
        $this->attributes['headers'] = empty($headers) ? null : Crypt::encryptString(json_encode($headers));
    }

    public function getHeaders(): array
    {
        $raw = $this->attributes['headers'] ?? null;
        if (!$raw) return [];
        try {
            return json_decode(Crypt::decryptString($raw), true) ?: [];
        } catch (\Throwable $e) {
            return [];
        }
    }
}
