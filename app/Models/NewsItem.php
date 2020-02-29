<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\HasEditorJsContent;
use App\Traits\HasPaperclip;
use Czim\Paperclip\Config\Steps\ResizeStep;
use Czim\Paperclip\Config\Variant;
use Czim\Paperclip\Contracts\AttachableInterface;
use Czim\Paperclip\Model\PaperclipTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;

/**
 * A news article
 * @author Roelof Roos <github@roelof.io>
 * @license MPL-2.0
 * @property-read AttachmentInterface $image
 */
class NewsItem extends SluggableModel implements AttachableInterface
{
    use PaperclipTrait;
    use HasPaperclip;
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
     * The attributes that should be mutated to dates.
     * @var array
     */
    protected $dates = [
        'created_at',
        'updated_at',
        'published_at',
    ];

    /**
     * Binds paperclip files
     * @return void
     */
    protected function bindPaperclip(): void
    {
        // Sizes
        $size = (object) [
            'article' => [1440, 960],
            'cover' => [384, 256]
        ];

        // The actual screenshots
        $this->hasAttachedFile('image', [
            'storage' => 'paperclip-public',
            'variants' => [
                // Article
                Variant::make('article')->steps(
                    ResizeStep::make()->width($size->article[0])->height($size->article[1])
                ),
                Variant::make('article-2x')->steps(
                    ResizeStep::make()->width($size->article[0] * 2)->height($size->article[1] * 2)
                ),

                // Covers
                Variant::make('cover')->steps([
                    ResizeStep::make()->width($size->cover[0])->height($size->cover[1])
                ]),
                Variant::make('cover-2x')->steps([
                    ResizeStep::make()->width($size->cover[0] * 2)->height($size->cover[1] * 2)
                ]),
            ]
        ]);
    }

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
    public function scopeAvailable(Builder $query): Builder
    {
        return $query
            ->orderByDesc('published_at')
            ->where(static function ($query) {
                $query->where('published_at', '<', now())
                    ->orWhereNull('published_at');
            });
    }
}
