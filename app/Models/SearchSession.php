<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class SearchSession extends Model
{
    use HasFactory;

    protected $table = 'search_sessions';

    protected $fillable = [
        'prompt',
        'status',
        'search_queries',
        'error_message',
    ];

    protected function casts(): array
    {
        return [
            'search_queries' => 'array',
        ];
    }

    public function aiPrompts(): HasMany
    {
        return $this->hasMany(AiPrompt::class, 'search_session_id');
    }

    public function newsSources(): HasMany
    {
        return $this->hasMany(NewsSource::class, 'search_session_id');
    }

    public function newsSummary(): HasOne
    {
        return $this->hasOne(NewsSummary::class, 'search_session_id');
    }
}
