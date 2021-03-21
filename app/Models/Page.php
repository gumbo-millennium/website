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
 * A user-generated page
 *
 * @property-read AttachmentInterface $image
 * @property int $id
 * @property \Illuminate\Support\Date $created_at
 * @property \Illuminate\Support\Date $updated_at
 * @property string $title
 * @property string $slug
 * @property string|null $group
 * @property string $type
 * @property string|null $summary
 * @property array|null $contents
 * @property int|null $author_id
 * @property string|null $image_file_name image name
 * @property int|null $image_file_size image size (in bytes)
 * @property string|null $image_content_type image content type
 * @property string|null $image_updated_at image update timestamp
 * @property array|null $image_variants image variants (json)
 * @property-read User|null $author
 * @property-read string|null $html
 * @property-read string $url
 */
class Page extends SluggableModel implements AttachableInterface
{
    use HasEditorJsContent;
    use HasPaperclip;
    use HasSimplePaperclippedMedia;
    use PaperclipTrait;

    public const TYPE_USER = 'user';
    public const TYPE_REQUIRED = 'required';
    public const TYPE_GIT = 'git';

    /**
     * Pages required to exist, cannot be deleted or renamed
     */
    public const REQUIRED_PAGES = [
        'home' => 'Homepage',
        'bestuur' => 'Het bestuur van Gumbo Millennium',
        'error-404' => 'Not Found',
        'over' => 'Over gumbo',
        'word-lid' => 'Nieuw lid pagina',
    ];

    public const SLUG_HOMEPAGE = 'home';
    public const SLUG_404 = 'error-404';

    /**
     * Returns pages required by the system, all in the main group
     *
     * @return array
     */
    public static function getRequiredPages(): array
    {
        return array_merge(self::REQUIRED_PAGES, config('gumbo.page-groups'));
    }

    /**
     * @inheritDoc
     */
    protected $fillable = [
        'slug',
        'title',
        'contents',
        'type',
    ];

    /**
     * @inheritDoc
     */
    protected $casts = [
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
                'unique' => true,
            ],
        ];
    }

    public function scopeHome(Builder $query): Builder
    {
        return $query->where('slug', 'homepage');
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

    /**
     * Returns the URL to this page
     *
     * @return string
     * @throws LogicException
     */
    public function getUrlAttribute(): string
    {
        if ($this->group) {
            return route('group.show', $this->only('group', 'slug'));
        }

        return url("/{$this->slug}");
    }

    /**
     * Binds paperclip files
     *
     * @return void
     */
    protected function bindPaperclip(): void
    {
        // Sizes
        $this->createSimplePaperclip('image', [
            'article' => [1440, 960, false],
            'cover' => [384, 256, true],
            'poster' => [192, 256, false],
        ]);
    }
}
