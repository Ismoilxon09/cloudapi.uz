<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Feedback extends Model
{
    protected $table = 'feedbacks';

    protected $fillable = [
        'user_id',
        'telegram_id',
        'name',
        'rating',
        'text',
        'admin_reply',
        'replied_at',
        'is_published',
        'is_featured',
        'source',
    ];

    protected $casts = [
        'rating' => 'integer',
        'is_published' => 'boolean',
        'is_featured' => 'boolean',
        'replied_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Landing sahifasida ko'rsatiladigan feedback'lar
     * (published + text bo'sh emas)
     */
    public static function forLanding(int $limit = 12)
    {
        return static::where('is_published', 1)
            ->whereNotNull('text')
            ->where('text', '!=', '')
            ->orderByDesc('is_featured')
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }

    /**
     * User's display info (name + avatar initial)
     */
    public function getDisplayNameAttribute(): string
    {
        if ($this->user) return $this->user->name ?: 'Foydalanuvchi';
        return $this->name ?: 'Foydalanuvchi';
    }

    public function getInitialAttribute(): string
    {
        $name = $this->display_name;
        return mb_strtoupper(mb_substr($name, 0, 1));
    }

    /**
     * Avatar color (name'ga qarab)
     */
    public function getAvatarColorAttribute(): string
    {
        $colors = ['#7C3AED', '#EC4899', '#3B82F6', '#10B981', '#F59E0B', '#EF4444', '#8B5CF6', '#06B6D4'];
        $hash = crc32($this->display_name);
        return $colors[abs($hash) % count($colors)];
    }
}