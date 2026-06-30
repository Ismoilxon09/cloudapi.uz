<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BlockedIp extends Model
{
    protected $table = 'blocked_ips';

    protected $fillable = [
        'ip_address',
        'reason',
        'blocked_until',
        'attempts_count',
        'is_permanent',
        'notes',
    ];

    protected $casts = [
        'blocked_until' => 'datetime',
        'is_permanent' => 'boolean',
        'attempts_count' => 'integer',
    ];

    /**
     * IP hozir bloklanganmi?
     */
    public function isActive(): bool
    {
        if ($this->is_permanent) return true;
        if (!$this->blocked_until) return false;
        return $this->blocked_until->isFuture();
    }

    /**
     * Qancha vaqt qoldi
     */
    public function remainingTime(): ?string
    {
        if ($this->is_permanent) return 'Doimiy';
        if (!$this->blocked_until) return null;
        if ($this->blocked_until->isPast()) return null;
        return $this->blocked_until->diffForHumans(null, true);
    }
}