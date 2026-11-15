<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiPrompt extends Model
{
    use HasFactory;

    protected $table = 'ai_prompts';

    protected $fillable = [
        'search_session_id',
        'prompt_type',
        'system_prompt',
        'user_prompt',
        'ai_response',
        'model',
        'tokens_used',
        'status',
        'error_message',
    ];

    public function searchSession(): BelongsTo
    {
        return $this->belongsTo(SearchSession::class, 'search_session_id');
    }
}
