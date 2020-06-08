<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\HasEditorJsContent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;

/**
 * A user-generated page
 * @author Roelof Roos <github@roelof.io>
 * @license MPL-2.0
 * @property-read AttachmentInterface $image
 */
class Page extends SluggableModel
{
    use HasEditorJsContent;

    public const TYPE_USER = 'user';
    public const TYPE_REQUIRED = 'required';
    public const TYPE_GIT = 'git';

    public const FILE_DISK = 'public';
    public const FILE_PATH = 'media/pages';

    /**
     * Pages required to exist, cannot be deleted or renamed
     */
    public const REQUIRED_PAGES = [
        'home' => 'Homepage',
        'error-404' => 'Not Found',
        'about' => 'Over gumbo',
        'word-lid' => 'Nieuw lid pagina',
    ];

    public const SLUG_HOMEPAGE = 'home';
    public const SLUG_404 = 'error-404';

    /**
     * Returns pages required by the system, all in the main group
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
        'type'
    ];

    /**
     * @inheritDoc
     */
    protected $casts = [
        'contents' => 'json',
        'user_id' => 'int',
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

    public function scopeHome(Builder $query): Builder
    {
        return $query->where('slug', 'homepage');
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
     * Returns the URL to this page
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
}
