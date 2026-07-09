<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ChatMessage extends Model
{
    protected $table = 'chat_messages';

    protected $fillable = [
        'session_id',
        'user_id',
        'role',
        'content',
        'model_id',
        'tokens_input',
        'tokens_output',
        'cost_uzs',
        'finish_reason',
        'error',
        'is_streaming',
        'is_regenerated',
        'parent_id',
        'metadata',
    ];

    protected $casts = [
        'tokens_input' => 'integer',
        'tokens_output' => 'integer',
        'cost_uzs' => 'float',
        'is_streaming' => 'boolean',
        'is_regenerated' => 'boolean',
        'metadata' => 'array',
    ];

    public function session(): BelongsTo
    {
        return $this->belongsTo(ChatSession::class, 'session_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(ChatAttachment::class, 'message_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(ChatMessage::class, 'parent_id');
    }

    public function regenerations(): HasMany
    {
        return $this->hasMany(ChatMessage::class, 'parent_id');
    }
}