<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class NewsSource extends Model
{
    use HasFactory;

    protected $table = 'news_sources';

    protected $fillable = [
        'search_session_id',
        'source_name',
        'title',
        'url',
        'canonical_url',
        'domain',
        'snippet',
        'raw_content',
        'clean_content',
        'author',
        'published_at',
        'relevance_score',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
            'relevance_score' => 'float',
        ];
    }

    public function searchSession(): BelongsTo
    {
        return $this->belongsTo(SearchSession::class, 'search_session_id');
    }

    public function newsSummaries(): BelongsToMany
    {
        return $this->belongsToMany(NewsSummary::class, 'summary_sources')
            ->withPivot('relevance_note')
            ->withTimestamps();
    }
}
