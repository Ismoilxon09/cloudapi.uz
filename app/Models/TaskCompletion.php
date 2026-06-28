<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaskCompletion extends Model
{
    protected $fillable = ['user_id', 'task_id', 'reward_gp', 'completed_at'];

    public $timestamps = false;

    protected $casts = [
        'reward_gp' => 'integer',
        'completed_at' => 'datetime',
    ];

    protected $attributes = [];

    protected static function booted()
    {
        static::creating(function ($model) {
            if (!$model->completed_at) {
                $model->completed_at = now();
            }
        });
    }

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function task(): BelongsTo { return $this->belongsTo(Task::class); }
}