<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiSupportMessage extends Model
{
    protected $fillable = [
        'conversation_id',
        'role',
        'content',
        'knowledge_refs',
        'model',
        'prompt_tokens',
        'completion_tokens',
        'needs_human',
        'feedback',
        'feedback_note',
        'training_approved',
        'meta',
    ];

    protected function casts(): array
    {
        return [
            'knowledge_refs' => 'array',
            'meta' => 'array',
            'needs_human' => 'boolean',
            'training_approved' => 'boolean',
            'prompt_tokens' => 'integer',
            'completion_tokens' => 'integer',
            'feedback' => 'integer',
        ];
    }

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(AiSupportConversation::class, 'conversation_id');
    }
}
