<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class NewsSummary extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'news_summaries';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'search_session_id',
        'title',
        'summary',
        'content',
        'key_points',
        'chronology',
        'source_comparison',
        'unconfirmed_facts',
        'conclusion',
        'category',
        'ai_model',
        'published_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'key_points' => 'array',
            'published_at' => 'datetime',
        ];
    }

    /**
     * Get the search session that owns this summary.
     */
    public function searchSession(): BelongsTo
    {
        return $this->belongsTo(SearchSession::class, 'search_session_id');
    }

    /**
     * Get the news sources linked through summary_sources.
     */
    public function sources(): BelongsToMany
    {
        return $this->belongsToMany(NewsSource::class, 'summary_sources')
            ->withPivot('relevance_note')
            ->withTimestamps();
    }

    /**
     * Get the references associated with the summary.
     */
    public function references(): HasMany
    {
        return $this->hasMany(NewsReference::class, 'news_summary_id');
    }
}
