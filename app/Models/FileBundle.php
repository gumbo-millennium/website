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
 * A bundle of uploaded files.
 *
 * @property int $id
 * @property null|int $category_id
 * @property null|\Illuminate\Support\Carbon $created_at
 * @property null|\Illuminate\Support\Carbon $updated_at
 * @property \Illuminate\Support\Carbon $published_at
 * @property null|int $owner_id
 * @property string $title
 * @property string $slug
 * @property null|string $description
 * @property int $total_size
 * @property string $sort_order
 * @property-read null|\App\Models\FileCategory $category
 * @property-read \App\Models\FileDownload[]|\Illuminate\Database\Eloquent\Collection $downloads
 * @property-read bool $is_available
 * @property-read null|string $url
 * @property-read \App\Models\Media[]|\Illuminate\Database\Eloquent\Collection $media
 * @property-read null|\App\Models\User $owner
 * @method static \Illuminate\Database\Eloquent\Builder|SluggableModel findSimilarSlugs(string $attribute, array $config, string $slug)
 * @method static Builder|FileBundle newModelQuery()
 * @method static Builder|FileBundle newQuery()
 * @method static Builder|FileBundle query()
 * @method static Builder|FileBundle whereAvailable()
 * @method static \Illuminate\Database\Eloquent\Builder|SluggableModel whereSlug(string $slug)
 * @mixin \Eloquent
 */
class FileBundle extends SluggableModel implements HasMedia
{
    use HasMediaTrait;
    use Searchable;

    /**
     * The model's attributes.
     *
     * @var array
     */
    protected $attributes = [
        'sort_order' => 'desc',
    ];

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
     * Hide non-released bundles.
     *
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
     * Generate the slug based on the display_title property.
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
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(FileCategory::class, 'category_id', 'id');
    }

    /**
     * A file has an owner.
     *
     * @return BelongsTo
     */
    public function owner(): Relation
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    /**
     * Returns the absolute URL to the file.
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
     * Configure the collection to privately store the data.
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
     * Returns the downloads.
     */
    public function downloads(): HasMany
    {
        return $this->hasMany(FileDownload::class, 'bundle_id');
    }

    /**
     * Returns if the bundle is available.
     */
    public function getIsAvailableAttribute(): bool
    {
        return $this->published_at === null || $this->published_at < now();
    }

    /**
     * Prevent searching non-published files.
     *
     * @return bool
     */
    public function shouldBeSearchable()
    {
        return $this->is_available;
    }
}
