<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DailyCheckin extends Model
{
    protected $fillable = ['user_id', 'checkin_date', 'reward_gp', 'streak'];

    public $timestamps = false;

    protected $casts = [
        'checkin_date' => 'date',
        'reward_gp' => 'integer',
        'streak' => 'integer',
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