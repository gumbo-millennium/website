<?php

namespace App\Models;

use App\Models\Traits\HasEditorJsContent;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Relation;

/**
 * A news article
 *
 * @author Roelof Roos <github@roelof.io>
 * @license MPL-2.0
 */
class NewsItem extends SluggableModel
{
    use HasEditorJsContent;

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
     * Generate the slug based on the title property
     *
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
     *
     * @return BelongsTo
     */
    public function author(): Relation
    {
        return $this->belongsTo(User::class, 'author_id', 'id');
    }

    /**
     * Converts contents to HTML
     *
     * @return string|null
     */
    public function getHtmlAttribute(): ?string
    {
        return $this->convertToHtml($this->contents);
    }
}
