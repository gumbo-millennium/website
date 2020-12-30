<?php

declare(strict_types=1);

namespace App\Models;

use App\Scopes\DefaultOrderScope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\Relation;

/**
 * A file category, containing file bundles
 *
 * @author Roelof Roos <github@roelof.io>
 * @license MPL-2.0
 */
class FileCategory extends SluggableModel
{
    /**
     * Add a check to automatically sort by title if none is set
     */
    public static function boot()
    {
        // Forward to parent
        parent::boot();

        // Order by title by default
        static::addGlobalScope(new DefaultOrderScope('title'));
    }

    /**
     * Find the default category
     *
     * @param array $columns
     * @return Model|Collection
     */
    public static function findDefault()
    {
        // Find first default category, or create one
        return static::firstOrCreate(['default' => true], [
            'title' => 'Overig',
        ]);
    }

    /**
     * Categories don't have timestamps.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * Title and default are fillable
     *
     * @var array
     */
    protected $fillable = ['title'];

    // Always auto-load files
    protected $with = [
        'bundles',
    ];

    /**
     * Slug on the category title
     *
     * @return array
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
     * The files that belong to this category
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
     * Returns download counts
     *
     * @return HasManyThrough
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
     * @param Builder $query
     * @return Builder
     * @throws InvalidArgumentException
     */
    public function scopeWhereAvailable(Builder $query): Builder
    {
        // phpcs:ignore SlevomatCodingStandard.Functions.RequireArrowFunction.RequiredArrowFunction
        return $query->whereHas('bundles', static function (Builder $query) {
            return $query->whereAvailable();
        });
    }

    /**
     * Attach all available scopes
     *
     * @param Builder $query
     * @return Builder
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
