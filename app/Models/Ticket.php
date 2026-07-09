<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Ticket extends Model
{
    protected $table = 'tickets';

    protected $fillable = [
        'user_id',
        'telegram_id',
        'subject',
        'message',
        'status',
        'priority',
        'admin_reply',
        'admin_id',
        'replied_at',
        'closed_at',
        'source',
    ];

    protected $casts = [
        'replied_at' => 'datetime',
        'closed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    // Scope: user'ning ticketlari
    public function scopeForUser($query, $userId, $telegramId = null)
    {
        return $query->where(function ($q) use ($userId, $telegramId) {
            $q->where('user_id', $userId);
            if ($telegramId) {
                $q->orWhere('telegram_id', $telegramId);
            }
        });
    }

    // Status labels
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'open' => 'Ochiq',
            'answered' => 'Javob berilgan',
            'in_progress' => 'Ko\'rib chiqilmoqda',
            'closed' => 'Yopilgan',
            default => 'Noma\'lum',
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'open' => '#F59E0B',        // yellow
            'answered' => '#10B981',    // green
            'in_progress' => '#3B82F6', // blue
            'closed' => '#6B7280',      // gray
            default => '#6B7280',
        };
    }

    public function getPriorityLabelAttribute(): string
    {
        return match ($this->priority) {
            'low' => 'Past',
            'normal' => 'Odatiy',
            'high' => 'Yuqori',
            'urgent' => 'Shoshilinch',
            default => 'Odatiy',
        };
    }
}