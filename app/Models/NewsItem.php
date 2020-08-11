<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\HasEditorJsContent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Storage;

/**
 * A news article
 * @author Roelof Roos <github@roelof.io>
 * @license MPL-2.0
 */
class NewsItem extends SluggableModel
{
    use HasEditorJsContent;

    /**
     * The "booted" method of the model.
     * @return void
     */
    protected static function booted()
    {
        static::saving(static function (NewsItem $newsItem) {
            $newsItem->published_at ??= now();
        });
    }

    /**
     * @inheritDoc
     */
    protected $fillable = [
        'slug',
        'title',
        'contents'
    ];

    /**
     * @inheritDoc
     */
    protected $casts = [
        'content' => 'json',
        'user_id' => 'int',
    ];

    /**
     * The attributes that should be mutated to dates.
     * @var array
     */
    protected $dates = [
        'created_at',
        'updated_at',
        'published_at',
    ];

    /**
     * Generate the slug based on the title property
     * @return array
     */
    public function sluggable(): array
    {
        return [
            'slug' => [
                'source' => 'title',
                'unique' => true
            ]
        ];
    }

    /**
     * Returns the owning user, if present
     * @return BelongsTo
     */
    public function author(): Relation
    {
        return $this->belongsTo(User::class, 'author_id', 'id');
    }

    /**
     * Converts contents to HTML
     * @return string|null
     */
    public function getHtmlAttribute(): ?string
    {
        return $this->convertToHtml($this->contents);
    }

    /**
     * Scope to available posts
     * @param Builder $query
     * @return Illuminate\Database\Eloquent\Builder
     * @throws InvalidArgumentException
     */
    public function scopeWhereAvailable(Builder $query): Builder
    {
        return $query
            ->orderByDesc('published_at')
            ->where(static function ($query) {
                $query->where('published_at', '<', now())
                    ->orWhereNull('published_at');
            });
    }

    /**
     * URL to the image's original size
     * @return null|string
     */
    public function getImageUrlAttribute(): ?string
    {
        return $this->image ? Storage::disk('public')->url($this->image) : null;
    }
}
