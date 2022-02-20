<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\HasEditorJsContent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\Relation;

/**
 * App\Models\Page.
 *
 * @property int $id
 * @property null|\Illuminate\Support\Carbon $created_at
 * @property null|\Illuminate\Support\Carbon $updated_at
 * @property string $title
 * @property string $slug
 * @property null|string $cover
 * @property null|string $group
 * @property string $type
 * @property null|string $summary
 * @property null|mixed $contents
 * @property null|int $author_id
 * @property bool $hidden
 * @property-read null|\App\Models\User $author
 * @property-read null|string $html
 * @property-read string $url
 * @method static \Database\Factories\PageFactory factory(...$parameters)
 * @method static \Illuminate\Database\Eloquent\Builder|SluggableModel findSimilarSlugs(string $attribute, array $config, string $slug)
 * @method static Builder|Page home()
 * @method static Builder|Page newModelQuery()
 * @method static Builder|Page newQuery()
 * @method static Builder|Page query()
 * @method static \Illuminate\Database\Eloquent\Builder|SluggableModel whereSlug(string $slug)
 * @method static \Illuminate\Database\Eloquent\Builder|SluggableModel withUniqueSlugConstraints(\Illuminate\Database\Eloquent\Model $model, string $attribute, array $config, string $slug)
 * @mixin \Eloquent
 */
class Page extends SluggableModel
{
    use HasEditorJsContent;
    use HasFactory;

    public const TYPE_USER = 'user';

    public const TYPE_REQUIRED = 'required';

    public const TYPE_GIT = 'git';

    /**
     * Pages required to exist, cannot be deleted or renamed.
     */
    public const REQUIRED_PAGES = [
        'home' => 'Homepage',
        'bestuur' => 'Het bestuur van Gumbo Millennium',
        'error-404' => 'Not Found',
        'over' => 'Over gumbo',
        'word-lid' => 'Nieuw lid pagina',
        'lustrum' => 'Over het Lustrum',
    ];

    public const SLUG_HOMEPAGE = 'home';

    public const SLUG_404 = 'error-404';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'slug',
        'title',
        'contents',
        'type',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'user_id' => 'int',
        'hidden' => 'bool',
        'contents' => 'json',
    ];

    /**
     * Returns pages required by the system, all in the main group.
     */
    public static function getRequiredPages(): array
    {
        return array_merge(self::REQUIRED_PAGES, config('gumbo.page-groups'));
    }

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

    public function scopeHome(Builder $query): Builder
    {
        return $query->where('slug', 'homepage');
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
     * Returns the URL to this page.
     *
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
