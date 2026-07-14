<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AgentConversation extends Model
{
    protected $fillable = [
        'agent_id', 'channel_id', 'channel_type', 'external_chat_id', 'external_user_id',
        'title', 'meta', 'total_messages', 'last_message_at',
    ];

    protected $casts = [
        'meta'            => 'array',
        'last_message_at' => 'datetime',
    ];

    public function agent(): BelongsTo { return $this->belongsTo(Agent::class); }
    public function channel(): BelongsTo { return $this->belongsTo(AgentChannel::class); }
    public function messages(): HasMany { return $this->hasMany(AgentMessage::class, 'conversation_id'); }
}
