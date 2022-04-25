<?php

declare(strict_types=1);

namespace App\Models\Gallery;

use App\Models\Activity;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * App\Models\Gallery\Album.
 *
 * @property int $id
 * @property null|int $activity_id
 * @property null|int $user_id
 * @property string $name
 * @property string $slug
 * @property null|string $description
 * @property null|\Illuminate\Support\Carbon $created_at
 * @property null|\Illuminate\Support\Carbon $updated_at
 * @property null|\Illuminate\Support\Carbon $deleted_at
 * @property null|\Illuminate\Support\Carbon $editable_from
 * @property null|\Illuminate\Support\Carbon $editable_until
 * @property-read null|Activity $activity
 * @property-read \App\Models\Gallery\Photo[]|\Illuminate\Database\Eloquent\Collection $photos
 * @property-read null|User $user
 * @method static \Database\Factories\Gallery\AlbumFactory factory(...$parameters)
 * @method static \Illuminate\Database\Eloquent\Builder|Album newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Album newQuery()
 * @method static \Illuminate\Database\Query\Builder|Album onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Album query()
 * @method static \Illuminate\Database\Query\Builder|Album withTrashed()
 * @method static \Illuminate\Database\Query\Builder|Album withoutTrashed()
 * @mixin \Eloquent
 */
class Album extends Model
{
    use HasFactory;
    use SoftDeletes;

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'editable_from' => 'datetime',
        'editable_until' => 'datetime',
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
        return $this->hasMany(Photo::class);
    }
}
