<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\Relation;
use Laravel\Scout\Searchable;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\Models\Media;

/**
 * A bundle of uploaded files
 * @author Roelof Roos <github@roelof.io>
 * @license MPL-2.0
 */
class FileBundle extends SluggableModel implements HasMedia
{
    use InteractsWithMedia;
    use Searchable;

    /**
     * {@inheritDoc}
     */
    protected $appends = [
        'url'
    ];

    /**
     * {@inheritDoc}
     */
    protected $fillable = [
        'title',
    ];

    /**
     * {@inheritDoc}
     */
    protected $casts = [
        'total_size' => 'int',
    ];

    /**
     * {@inheritDoc}
     */
    protected $dates = [
        'published_at',
    ];

    /**
     * The relationship counts that should be eager loaded on every query.
     * @var array
     */
    protected $withCount = [
        'downloads'
    ];

    /**
     * Hide non-released bundles
     * @param Builder $query
     * @return Builder
     * @throws InvalidArgumentException
     */
    public function scopeWhereAvailable(Builder $query): Builder
    {
        // phpcs:ignore SlevomatCodingStandard.Functions.RequireArrowFunction.RequiredArrowFunction
        return $query->where(static function ($builder) {
            return $builder
                ->whereNull('published_at')
                ->has('media')
                ->orWhere('published_at', '<', now());
        });
    }

    /**
     * Generate the slug based on the display_title property
     * @return array
     */
    public function sluggable(): array
    {
        return [
            'slug' => [
                'source' => 'title',
                'unique' => true,
                'onUpdate' => false
            ]
        ];
    }

    /**
     * The roles that belong to the user.
     * @return BelongsTo
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(FileCategory::class, 'category_id', 'id');
    }

    /**
     * A file has an owner
     * @return BelongsTo
     */
    public function owner(): Relation
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    /**
     * Returns the absolute URL to the file
     * @return string|null
     */
    public function getUrlAttribute(): ?string
    {
        // Ignore if slugless
        if ($this->slug === null) {
            return null;
        }

        return route('files.show', ['bundle' => $this]);
    }


    /**
     * Configure the collection to privately store the data
     * @return void
     */
    public function registerMediaCollections(): void
    {
        $this
            ->addMediaCollection('default')
            ->useDisk('local');
    }

    /**
     * Returns the downloads
     * @return HasMany
     */
    public function downloads(): HasMany
    {
        return $this->hasMany(FileDownload::class, 'bundle_id');
    }

    /**
     * Returns if the bundle is available
     * @return bool
     */
    public function getIsAvailableAttribute(): bool
    {
        return $this->published_at === null || $this->published_at < now();
    }

    /**
     * Prevent searching non-published files
     * @return bool
     */
    public function shouldBeSearchable()
    {
        return $this->is_available;
    }
}
