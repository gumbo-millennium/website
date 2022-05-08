<?php

declare(strict_types=1);

namespace App\Models\Gallery;

use App\Enums\AlbumVisibility;
use App\Models\Activity;
use App\Models\User;
use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Request;

/**
 * App\Models\Gallery\Album.
 *
 * @property int $id
 * @property null|int $activity_id
 * @property null|int $user_id
 * @property string $name
 * @property string $slug
 * @property null|string $description
 * @property AlbumVisibility $visibility
 * @property null|\Illuminate\Support\Carbon $created_at
 * @property null|\Illuminate\Support\Carbon $updated_at
 * @property null|\Illuminate\Support\Carbon $deleted_at
 * @property null|\Illuminate\Support\Carbon $editable_from
 * @property null|\Illuminate\Support\Carbon $editable_until
 * @property-read null|Activity $activity
 * @property-read \App\Models\Gallery\Photo[]|\Illuminate\Database\Eloquent\Collection $allPhotos
 * @property-read null|\App\Models\Gallery\Photo $cover
 * @property-read null|string $cover_image
 * @property-read \App\Models\Gallery\Photo[]|\Illuminate\Database\Eloquent\Collection $photos
 * @property-read null|User $user
 * @method static \Database\Factories\Gallery\AlbumFactory factory(...$parameters)
 * @method static Builder|Album findSimilarSlugs(string $attribute, array $config, string $slug)
 * @method static Builder|Album forUser(\App\Models\User $user)
 * @method static Builder|Album newModelQuery()
 * @method static Builder|Album newQuery()
 * @method static \Illuminate\Database\Query\Builder|Album onlyTrashed()
 * @method static Builder|Album query()
 * @method static Builder|Album visible(?\App\Models\User $user = null)
 * @method static \Illuminate\Database\Query\Builder|Album withTrashed()
 * @method static Builder|Album withUniqueSlugConstraints(\Illuminate\Database\Eloquent\Model $model, string $attribute, array $config, string $slug)
 * @method static \Illuminate\Database\Query\Builder|Album withoutTrashed()
 * @mixin \Eloquent
 */
class Album extends Model
{
    use HasFactory;
    use Sluggable;
    use SoftDeletes;

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'visibility' => AlbumVisibility::class,
        'editable_from' => 'datetime',
        'editable_until' => 'datetime',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'name',
        'slug',
        'description',
        'visibility',
    ];

    public function activity(): BelongsTo
    {
        return $this->belongsTo(Activity::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function photos(): HasMany
    {
        return $this->allPhotos()
            ->visible();
    }

    public function allPhotos(): HasMany
    {
        return $this->hasMany(Photo::class)
            ->orderBy('taken_at', 'desc')
            ->orderBy('created_at', 'desc')
            ->orderBy('id', 'desc');
    }

    public function cover(): HasOne
    {
        return $this->hasOne(Photo::class)
            ->latest('taken_at');
    }

    public function getCoverImageAttribute(): ?string
    {
        return $this->cover?->path;
    }

    public function scopeForUser(Builder $query, User $user): void
    {
        $query->where(function (Builder $query) use ($user) {
            // Check if the album is public
            $query->where('visibility', AlbumVisibility::Public);

            // Check if the album is private but $user is the owner
            $query->orWhere(fn ($query) => $query
                ->where('visibility', AlbumVisibility::Private)
                ->whereHas('user', fn ($query) => $query->where('id', $user->id)), );
        });
    }

    public function scopeVisible(Builder $query, ?User $user = null): void
    {
        $user ??= Request::user();

        $query->where(function (Builder $query) use ($user) {
            $query->where('visibility', AlbumVisibility::Public);
            if (! $user) {
                return;
            }

            $query->orWhere(fn (Builder $query) => $query
                ->where('visibility', AlbumVisibility::Private)
                ->whereHas('user', fn (Builder $query) => $query->where('id', $user->id)), );
        });
    }

    public function sluggable(): array
    {
        return [
            'slug' => [
                'source' => 'name',
                'maxLength' => 64,
                'reserved' => [
                    'photo',
                    'create',
                    'filepond',
                ],
                'includeTrashed' => true,
            ],
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
