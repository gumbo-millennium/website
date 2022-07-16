<?php

declare(strict_types=1);

namespace App\Models;

use Advoor\NovaEditorJs\NovaEditorJsCast;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\HtmlString;

/**
 * App\Models\NewsItem.
 *
 * @property int $id
 * @property null|\Illuminate\Support\Carbon $created_at
 * @property null|\Illuminate\Support\Carbon $updated_at
 * @property \Illuminate\Support\Carbon $published_at
 * @property string $title
 * @property string $slug
 * @property null|string $cover
 * @property string $category
 * @property null|string $sponsor
 * @property null|string $headline
 * @property null|\Advoor\NovaEditorJs\NovaEditorJsData $contents
 * @property null|int $author_id
 * @property-read null|\App\Models\User $author
 * @property-read null|\Illuminate\Support\HtmlString $html
 * @method static \Database\Factories\NewsItemFactory factory(...$parameters)
 * @method static \Illuminate\Database\Eloquent\Builder|SluggableModel findSimilarSlugs(string $attribute, array $config, string $slug)
 * @method static Builder|NewsItem newModelQuery()
 * @method static Builder|NewsItem newQuery()
 * @method static Builder|NewsItem query()
 * @method static Builder|NewsItem whereAvailable()
 * @method static \Illuminate\Database\Eloquent\Builder|SluggableModel whereSlug(string $slug)
 * @method static \Illuminate\Database\Eloquent\Builder|SluggableModel withUniqueSlugConstraints(\Illuminate\Database\Eloquent\Model $model, string $attribute, array $config, string $slug)
 * @mixin \Eloquent
 */
class NewsItem extends SluggableModel
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'slug',
        'title',
        'contents',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'contents' => NovaEditorJsCast::class,
        'user_id' => 'int',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'published_at' => 'datetime',
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
    public function getHtmlAttribute(): ?HtmlString
    {
        return $this->contents?->toHtml();
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
}
