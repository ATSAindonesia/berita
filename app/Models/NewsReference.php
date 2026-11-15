<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NewsReference extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'news_references';

    /**
     * Indicates if the model should be timestamped.
     * Note: Migration only has created_at without updated_at.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'news_summary_id',
        'source_name',
        'source_title',
        'source_url',
        'author',
        'published_at',
        'created_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
            'created_at' => 'datetime',
        ];
    }

    /**
     * Get the summary that owns the reference.
     */
    public function summary(): BelongsTo
    {
        return $this->belongsTo(NewsSummary::class, 'news_summary_id');
    }
}
