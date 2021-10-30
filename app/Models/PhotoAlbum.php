<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Request;

/**
 * App\Models\PhotoAlbum.
 *
 * @property int $id
 * @property null|int $user_id
 * @property string $name
 * @property null|string $description
 * @property string $visibility
 * @property null|\Illuminate\Support\Carbon $created_at
 * @property null|\Illuminate\Support\Carbon $updated_at
 * @property \Illuminate\Support\Carbon $published_at
 * @property-read \App\Models\Photo[]|\Illuminate\Database\Eloquent\Collection $photos
 * @property-read null|User $user
 * @method static \Illuminate\Database\Eloquent\Builder|Album newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Album newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Album query()
 * @mixin \Eloquent
 */
class PhotoAlbum extends Model
{
    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'published_at' => 'datetime',
    ];

    public static function boot()
    {
        parent::boot();

        static::creating(function (self $model) {
            $model->published_at ??= Date::now();

            if ($model->user === null) {
                $model->user()->associate(Request::instance()->user());
            }
        });
    }

    /**
     * The photos of this album.
     */
    public function photos(): HasMany
    {
        return $this->hasMany(Photo::class, 'album_id');
    }

    /**
     * The user who created this album.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
