<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\HasEditorJsContent;
use App\Models\Traits\HasSimplePaperclippedMedia;
use App\Traits\HasPaperclip;
use Czim\Paperclip\Contracts\AttachableInterface;
use Czim\Paperclip\Model\PaperclipTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;

/**
 * A news article.
 *
 * @property int $id
 * @property null|\Illuminate\Support\Carbon $created_at
 * @property null|\Illuminate\Support\Carbon $updated_at
 * @property \Illuminate\Support\Carbon $published_at
 * @property string $title
 * @property string $slug
 * @property string $category
 * @property null|string $sponsor
 * @property null|string $headline
 * @property null|mixed $contents
 * @property null|int $author_id
 * @property null|string $image_file_name image name
 * @property null|int $image_file_size image size (in bytes)
 * @property null|string $image_content_type image content type
 * @property null|string $image_updated_at image update timestamp
 * @property null|mixed $image_variants image variants (json)
 * @property-read null|\App\Models\User $author
 * @property-read null|string $html
 * @method static \Illuminate\Database\Eloquent\Builder|SluggableModel findSimilarSlugs(string $attribute, array $config, string $slug)
 * @method static Builder|NewsItem newModelQuery()
 * @method static Builder|NewsItem newQuery()
 * @method static Builder|NewsItem query()
 * @method static Builder|NewsItem whereAvailable()
 * @method static \Illuminate\Database\Eloquent\Builder|SluggableModel whereSlug(string $slug)
 * @mixin \Eloquent
 */
class NewsItem extends SluggableModel implements AttachableInterface
{
    use HasEditorJsContent;
    use HasPaperclip;
    use HasSimplePaperclippedMedia;
    use PaperclipTrait;

    /**
     * @inheritDoc
     */
    protected $fillable = [
        'slug',
        'title',
        'contents',
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
     *
     * @var array
     */
    protected $dates = [
        'created_at',
        'updated_at',
        'published_at',
    ];

    /**
     * Generate the slug based on the title property.
     */
    public function sluggable(): array
    {
        return [
            'slug' => [
                'source' => 'title',
                'unique' => true,
            ],
        ];
    }

    /**
     * Returns the owning user, if present.
     *
     * @return BelongsTo
     */
    public function author(): Relation
    {
        return $this->belongsTo(User::class, 'author_id', 'id');
    }

    /**
     * Converts contents to HTML.
     */
    public function getHtmlAttribute(): ?string
    {
        return $this->convertToHtml($this->contents);
    }

    /**
     * Scope to available posts.
     *
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
     * Binds paperclip files.
     */
    protected function bindPaperclip(): void
    {
        // Sizes
        $this->createSimplePaperclip('image', [
            'article' => [1440, 960, false],
            'cover' => [384, 256, false],
        ]);
    }
}
