<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SignupAttempt extends Model
{
    protected $table = 'signup_attempts';
    public $timestamps = false;

    protected $fillable = [
        'ip_address',
        'device_hash',
        'user_agent',
        'email',
        'phone',
        'oauth_provider',
        'oauth_id',
        'success',
        'user_id',
        'fraud_score',
        'blocked_reason',
        'country',
        'is_vpn',
        'created_at',
    ];

    protected $casts = [
        'success' => 'boolean',
        'is_vpn' => 'boolean',
        'fraud_score' => 'integer',
        'created_at' => 'datetime',
    ];

    protected static function booted()
    {
        static::creating(function ($model) {
            if (!$model->created_at) $model->created_at = now();
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}