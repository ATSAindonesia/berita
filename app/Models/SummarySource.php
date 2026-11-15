<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SummarySource extends Model
{
    use HasFactory;

    protected $table = 'summary_sources';

    protected $fillable = [
        'news_summary_id',
        'news_source_id',
        'relevance_note',
    ];

    public function newsSummary(): BelongsTo
    {
        return $this->belongsTo(NewsSummary::class, 'news_summary_id');
    }

    public function newsSource(): BelongsTo
    {
        return $this->belongsTo(NewsSource::class, 'news_source_id');
    }
}
