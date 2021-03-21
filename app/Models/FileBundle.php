<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\Relation;
use Laravel\Scout\Searchable;
use Spatie\MediaLibrary\HasMedia\HasMedia;
use Spatie\MediaLibrary\HasMedia\HasMediaTrait;
use Spatie\MediaLibrary\Models\Media;

/**
 * A bundle of uploaded files
 *
 * @property int $id
 * @property int|null $category_id
 * @property \Illuminate\Support\Date $created_at
 * @property \Illuminate\Support\Date $updated_at
 * @property \Illuminate\Support\Date $published_at
 * @property int|null $owner_id
 * @property string $title
 * @property string $slug
 * @property string|null $description
 * @property int $total_size
 * @property-read FileCategory|null $category
 * @property-read \Illuminate\Database\Eloquent\Collection<FileDownload> $downloads
 * @property-read bool $is_available
 * @property-read string|null $url
 * @property-read \Illuminate\Database\Eloquent\Collection<Media> $media
 * @property-read User|null $owner
 */
class FileBundle extends SluggableModel implements HasMedia
{
    use HasMediaTrait;
    use Searchable;

    /**
     * {@inheritDoc}
     */
    protected $appends = [
        'url',
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
     *
     * @var array
     */
    protected $withCount = [
        'downloads',
    ];

    /**
     * Hide non-released bundles
     *
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
     *
     * @return array
     */
    public function sluggable(): array
    {
        return [
            'slug' => [
                'source' => 'title',
                'unique' => true,
                'onUpdate' => false,
            ],
        ];
    }

    /**
     * The roles that belong to the user.
     *
     * @return BelongsTo
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(FileCategory::class, 'category_id', 'id');
    }

    /**
     * A file has an owner
     *
     * @return BelongsTo
     */
    public function owner(): Relation
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    /**
     * Returns the absolute URL to the file
     *
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
     *
     * @return void
     */
    public function registerMediaCollections()
    {
        $size = [840, 1190];
        $variants = [
            'thumb' => 1,
            'thumb-sm' => 0.5,
            'thumb-lg' => 2,
        ];

        $this
            ->addMediaCollection('default')
            ->useDisk('local')
            // phpcs:ignore SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
            ->registerMediaConversions(function (Media $media) use ($variants, $size) {
                foreach ($variants as $name => $mul) {
                    $this->addMediaConversion($name)
                        ->width($size[0] * $mul)
                        ->height($size[1] * $mul);
                }
            });
    }

    /**
     * Returns the downloads
     *
     * @return HasMany
     */
    public function downloads(): HasMany
    {
        return $this->hasMany(FileDownload::class, 'bundle_id');
    }

    /**
     * Returns if the bundle is available
     *
     * @return bool
     */
    public function getIsAvailableAttribute(): bool
    {
        return $this->published_at === null || $this->published_at < now();
    }

    /**
     * Prevent searching non-published files
     *
     * @return bool
     */
    public function shouldBeSearchable()
    {
        return $this->is_available;
    }
}
