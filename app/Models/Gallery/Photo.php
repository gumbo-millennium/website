<?php

declare(strict_types=1);

namespace App\Models\Gallery;

use App\Enums\PhotoVisibility;
use App\Models\User;
use App\Services\GalleryService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\App;

/**
 * App\Models\Gallery\Photo.
 *
 * @property int $id
 * @property int $album_id
 * @property null|int $user_id
 * @property PhotoVisibility $visibility
 * @property string $name
 * @property string $path
 * @property null|string $description
 * @property null|int $width
 * @property null|int $height
 * @property null|int $size
 * @property null|string $removal_reason
 * @property null|\Illuminate\Support\Carbon $taken_at
 * @property null|\Illuminate\Support\Carbon $created_at
 * @property null|\Illuminate\Support\Carbon $updated_at
 * @property null|\Illuminate\Support\Carbon $deleted_at
 * @property null|\Illuminate\Support\Collection $exif
 * @property-read \App\Models\Gallery\Album $album
 * @property-read null|string $aspect_ratio
 * @property-read bool $is_visible
 * @property-read null|\static $next_photo
 * @property-read null|\static $previous_photo
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Gallery\PhotoReaction> $reactions
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Gallery\PhotoReport> $reports
 * @property-read null|User $user
 * @method static Builder|Photo editable()
 * @method static \Database\Factories\Gallery\PhotoFactory factory($count = null, $state = [])
 * @method static Builder|Photo newModelQuery()
 * @method static Builder|Photo newQuery()
 * @method static Builder|Photo onlyTrashed()
 * @method static Builder|Photo query()
 * @method static Builder|Photo visible()
 * @method static Builder|Photo withTrashed()
 * @method static Builder|Photo withUserInteraction(\App\Models\User $user)
 * @method static Builder|Photo withoutTrashed()
 * @mixin \Eloquent
 */
class Photo extends Model
{
    use HasFactory;
    use SoftDeletes;

    /**
     * The model's attributes.
     *
     * @var array
     */
    protected $attributes = [
        'visibility' => PhotoVisibility::Visible,
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'visibility' => PhotoVisibility::class,
        'taken_at' => 'datetime',
        'exif' => 'collection',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'aspect_ratio',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
        'album_id',
        'user_id',
        'removal_reason',
        'is_visible',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'visibility',
        'name',
        'path',
        'description',
    ];

    public function album(): BelongsTo
    {
        return $this->belongsTo(Album::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reactions(): HasMany
    {
        return $this->hasMany(PhotoReaction::class);
    }

    public function reports(): HasMany
    {
        return $this->hasMany(PhotoReport::class);
    }

    public function getPreviousPhotoAttribute(): ?static
    {
        return App::make(GalleryService::class)->photoBefore($this);
    }

    public function getNextPhotoAttribute(): ?static
    {
        return App::make(GalleryService::class)->photoAfter($this);
    }

    public function scopeVisible(Builder $query): void
    {
        $query->where('visibility', PhotoVisibility::Visible);
    }

    public function scopeEditable(Builder $query): void
    {
        $query->whereIn('visibility', [
            PhotoVisibility::Visible,
            PhotoVisibility::Hidden,
        ]);
    }

    public function scopeWithUserInteraction(Builder $query, User $user): void
    {
        $onlyThisUser = fn (Builder $query) => $query->where('user_id', $user->id);

        $query
            ->with('reactions', $onlyThisUser)
            ->with('reports', $onlyThisUser);
    }

    public function getIsVisibleAttribute(): bool
    {
        return $this->visibility == PhotoVisibility::Visible;
    }

    /**
     * Get the aspect ratio of the photo, if known.
     */
    public function getAspectRatioAttribute(): ?string
    {
        // Assume square if no dimensions are set
        if (! $this->width || ! $this->height) {
            return null;
        }

        return "{$this->width} / {$this->height}";
    }
}
