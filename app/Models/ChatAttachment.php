<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChatAttachment extends Model
{
    protected $table = 'chat_attachments';

    // Jadvalda faqat created_at bor (updated_at yo'q)
    const UPDATED_AT = null;

    protected $fillable = [
        'message_id',
        'user_id',
        'type',
        'filename',
        'original_name',
        'mime_type',
        'size_bytes',
        'path',
        'url',
        'width',
        'height',
        'duration_sec',
        'metadata',
    ];

    protected $casts = [
        'size_bytes' => 'integer',
        'width' => 'integer',
        'height' => 'integer',
        'duration_sec' => 'integer',
        'metadata' => 'array',
    ];

    public function message(): BelongsTo
    {
        return $this->belongsTo(ChatMessage::class, 'message_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getFullUrlAttribute(): string
    {
        if ($this->url) return $this->url;
        return asset('storage/' . $this->path);
    }
}