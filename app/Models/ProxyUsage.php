<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProxyUsage extends Model
{
    use HasFactory;

    protected $table = 'proxy_usage';
    public $timestamps = false;

    protected $fillable = [
        'proxy_key_id', 'user_id', 'model', 'provider',
        'tokens_in', 'tokens_out', 'cost_usd', 'cost_uzs',
        'latency_ms', 'status_code', 'error',
        'ip', 'user_agent', 'created_at',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'cost_usd'   => 'decimal:8',
            'cost_uzs'   => 'decimal:4',
        ];
    }

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function proxyKey(): BelongsTo { return $this->belongsTo(ProxyKey::class); }
}