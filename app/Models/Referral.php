<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Referral extends Model
{
    public $timestamps = false;

    protected $fillable = ['referrer_id', 'referred_id', 'bonus_gp', 'bonus_paid'];

    protected $casts = [
        'bonus_gp' => 'integer',
        'bonus_paid' => 'boolean',
        'created_at' => 'datetime',
    ];

    protected static function booted()
    {
        static::creating(function ($m) {
            if (!$m->created_at) $m->created_at = now();
        });
    }

    public function referrer(): BelongsTo { return $this->belongsTo(User::class, 'referrer_id'); }
    public function referred(): BelongsTo { return $this->belongsTo(User::class, 'referred_id'); }
}