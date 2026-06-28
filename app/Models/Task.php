<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Task extends Model
{
    protected $fillable = [
        'channel_id', 'channel_name', 'channel_url',
        'reward_gp', 'active', 'sort_order',
    ];

    protected $casts = [
        'active' => 'boolean',
        'reward_gp' => 'integer',
        'sort_order' => 'integer',
    ];

    public function completions(): HasMany
    {
        return $this->hasMany(TaskCompletion::class);
    }

    public function scopeActive($q) { return $q->where('active', true); }
}