<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ChatSession extends Model
{
    protected $table = 'chat_sessions';

    protected $fillable = [
        'user_id',
        'title',
        'model_id',
        'system_prompt',
        'temperature',
        'max_tokens',
        'total_messages',
        'total_tokens_input',
        'total_tokens_output',
        'total_cost_uzs',
        'is_pinned',
        'is_archived',
        'last_message_at',
        'metadata',
    ];

    protected $casts = [
        'temperature' => 'float',
        'max_tokens' => 'integer',
        'total_messages' => 'integer',
        'total_tokens_input' => 'integer',
        'total_tokens_output' => 'integer',
        'total_cost_uzs' => 'float',
        'is_pinned' => 'boolean',
        'is_archived' => 'boolean',
        'last_message_at' => 'datetime',
        'metadata' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(ChatMessage::class, 'session_id')->orderBy('created_at');
    }

    public function lastMessage(): HasMany
    {
        return $this->hasMany(ChatMessage::class, 'session_id')->latest()->limit(1);
    }

    /**
     * Add message and update counters
     */
    public function recordMessage(ChatMessage $message): void
    {
        $this->increment('total_messages');
        if ($message->tokens_input) $this->increment('total_tokens_input', $message->tokens_input);
        if ($message->tokens_output) $this->increment('total_tokens_output', $message->tokens_output);
        if ($message->cost_uzs) $this->increment('total_cost_uzs', $message->cost_uzs);
        $this->update([
            'last_message_at' => now(),
            'model_id' => $message->model_id ?: $this->model_id,
        ]);
    }

    /**
     * Auto-generate title from first user message
     */
    public function generateTitle(string $firstMessage): void
    {
        $title = mb_substr(trim($firstMessage), 0, 60);
        if (mb_strlen($firstMessage) > 60) $title .= '...';
        $this->update(['title' => $title ?: 'Yangi chat']);
    }
}