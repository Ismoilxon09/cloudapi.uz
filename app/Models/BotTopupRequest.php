<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BotTopupRequest extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id', 'amount_uzs', 'screenshot_file_id', 'screenshot_path',
        'note', 'status', 'admin_id', 'admin_note', 'reviewed_at',
    ];

    protected $casts = [
        'amount_uzs' => 'decimal:2',
        'reviewed_at' => 'datetime',
        'created_at' => 'datetime',
    ];

    protected static function booted()
    {
        static::creating(function ($m) {
            if (!$m->created_at) $m->created_at = now();
        });
    }

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
}