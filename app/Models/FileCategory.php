<?php

declare(strict_types=1);

namespace App\Models;

use App\Scopes\DefaultOrderScope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\Relation;

/**
 * App\Models\FileCategory.
 *
 * @property int $id
 * @property null|string $created_at
 * @property null|string $updated_at
 * @property null|string $title
 * @property string $slug
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\FileBundle> $bundles
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\FileDownload> $downloads
 * @method static \Database\Factories\FileCategoryFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder|SluggableModel findSimilarSlugs(string $attribute, array $config, string $slug)
 * @method static Builder|FileCategory newModelQuery()
 * @method static Builder|FileCategory newQuery()
 * @method static Builder|FileCategory query()
 * @method static Builder|FileCategory whereAvailable()
 * @method static \Illuminate\Database\Eloquent\Builder|SluggableModel whereSlug(string $slug)
 * @method static Builder|FileCategory withAvailable()
 * @method static \Illuminate\Database\Eloquent\Builder|SluggableModel withUniqueSlugConstraints(\Illuminate\Database\Eloquent\Model $model, string $attribute, array $config, string $slug)
 * @mixin \Eloquent
 */
class FileCategory extends SluggableModel
{
    use HasFactory;

    /**
     * Categories don't have timestamps.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * Title and default are fillable.
     *
     * @var array
     */
    protected $fillable = ['title'];

    // Always auto-load files
    protected $with = [
        'bundles',
    ];

    /**
     * Add a check to automatically sort by title if none is set.
     */
    public static function boot()
    {
        // Forward to parent
        parent::boot();

        // Order by title by default
        static::addGlobalScope(new DefaultOrderScope('title'));
    }

    /**
     * Find the default category.
     *
     * @return Collection|Model
     */
    public static function findDefault()
    {
        // Find first default category, or create one
        return static::firstOrCreate(['default' => true], [
            'title' => 'Overig',
        ]);
    }

    /**
     * Slug on the category title.
     */
    public function sluggable(): array
    {
        return [
            'slug' => [
                'source' => 'title',
                'unique' => true,
                'reserved' => ['add', 'edit', 'remove'],
            ],
        ];
    }

    /**
     * The files that belong to this category.
     *
     * @return HasMany
     */
    public function bundles(): Relation
    {
        return $this
            ->hasMany(FileBundle::class, 'category_id', 'id')
            ->orderByDesc('published_at')
            ->orderByDesc('updated_at')
            ->orderBy('title');
    }

    /**
     * Returns download counts.
     */
    public function downloads(): HasManyThrough
    {
        return $this->hasManyThrough(
            FileDownload::class,
            FileBundle::class,
            'category_id',
            'bundle_id',
        );
    }

    /**
     * Hide categories that are empty or only have non-released bundles.
     *
     * @throws InvalidArgumentException
     */
    public function scopeWhereAvailable(Builder $query): Builder
    {
        // phpcs:ignore SlevomatCodingStandard.Functions.RequireArrowFunction.RequiredArrowFunction
        return $query->whereHas('bundles', static fn (Builder $query) => $query->whereAvailable());
    }

    /**
     * Attach all available scopes.
     */
    public function scopeWithAvailable(Builder $query): Builder
    {
        // phpcs:ignore SlevomatCodingStandard.Functions.RequireArrowFunction.RequiredArrowFunction
        return $query->with(['bundles' => static function (HasMany $query) {
            $query
                ->whereAvailable()
                ->orderByDesc('published_at')
                ->orderBy('title');
        }]);
    }
}
