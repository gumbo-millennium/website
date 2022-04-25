<?php

declare(strict_types=1);

namespace App\Models\Gallery;

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
 * @property int $visible
 * @property string $name
 * @property string $path
 * @property null|string $description
 * @property null|string $removal_reason
 * @property null|\Illuminate\Support\Carbon $taken_at
 * @property null|\Illuminate\Support\Carbon $created_at
 * @property null|\Illuminate\Support\Carbon $updated_at
 * @property null|\Illuminate\Support\Carbon $deleted_at
 * @property-read \App\Models\Gallery\Album $album
 * @property-read null|self $next_photo
 * @property-read null|self $previous_photo
 * @property-read null|User $user
 * @method static \Database\Factories\Gallery\PhotoFactory factory(...$parameters)
 * @method static Builder|Photo newModelQuery()
 * @method static Builder|Photo newQuery()
 * @method static \Illuminate\Database\Query\Builder|Photo onlyTrashed()
 * @method static Builder|Photo query()
 * @method static \Illuminate\Database\Query\Builder|Photo withTrashed()
 * @method static \Illuminate\Database\Query\Builder|Photo withoutTrashed()
 * @mixin \Eloquent
 */
class Photo extends Model
{
    use HasFactory;
    use SoftDeletes;

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'taken_at' => 'datetime',
    ];

    public function album(): BelongsTo
    {
        return $this->belongsTo(Album::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getPreviousPhotoAttribute(): ?self
    {
        return App::make(GalleryService::class)->photoBefore($this);
    }

    public function getNextPhotoAttribute(): ?self
    {
        return App::make(GalleryService::class)->photoAfter($this);
    }
}
