<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AgentMessage extends Model
{
    protected $fillable = [
        'conversation_id', 'agent_id', 'role', 'content', 'model_id',
        'tokens_input', 'tokens_output', 'cost_uzs', 'latency_ms', 'meta',
    ];

    protected $casts = [
        'tokens_input'  => 'integer',
        'tokens_output' => 'integer',
        'cost_uzs'      => 'decimal:4',
        'latency_ms'    => 'integer',
        'meta'          => 'array',
    ];

    public function conversation(): BelongsTo { return $this->belongsTo(AgentConversation::class, 'conversation_id'); }
    public function agent(): BelongsTo { return $this->belongsTo(Agent::class); }
}
