<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdminLog extends Model
{
    public $timestamps = false;
    protected $fillable = [
        'admin_id', 'action', 'target_type', 'target_id',
        'description', 'metadata', 'ip', 'user_agent',
    ];

    protected $casts = [
        'metadata' => 'array',
        'created_at' => 'datetime',
    ];

    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    /**
     * Tezkor log yozish
     */
    public static function record(string $action, $target = null, ?string $description = null, array $metadata = []): self
    {
        return self::create([
            'admin_id' => auth()->id(),
            'action' => $action,
            'target_type' => $target ? class_basename($target) : null,
            'target_id' => $target?->id,
            'description' => $description,
            'metadata' => $metadata,
            'ip' => request()->ip(),
            'user_agent' => substr(request()->userAgent() ?? '', 0, 500),
            'created_at' => now(),
        ]);
    }
}