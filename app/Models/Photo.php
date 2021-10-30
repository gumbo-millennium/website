<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Request;

/**
 * App\Models\Photo.
 *
 * @property int $id
 * @property int $album_id
 * @property null|int $user_id
 * @property string $caption
 * @property string $disk
 * @property string $path
 * @property null|\Illuminate\Support\Carbon $created_at
 * @property null|\Illuminate\Support\Carbon $updated_at
 * @property \Illuminate\Support\Carbon $taken_at
 * @property-read \App\Models\Album $album
 * @property-read null|User $user
 * @method static \Illuminate\Database\Eloquent\Builder|Photo newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Photo newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Photo query()
 * @mixin \Eloquent
 */
class Photo extends Model
{
    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'taken_at' => 'datetime',
    ];

    public static function boot(): void
    {
        parent::boot();

        static::creating(function (self $photo) {
            // Save disk
            $photo->disk = Config::get('gumbo.photos.storage-disk');

            // Save user, if any
            if ($user = Request::instance()->user()) {
                $photo->user()->associate($user);
            }
        });
    }

    /**
     * The album this photo belongs to.
     */
    public function album(): BelongsTo
    {
        return $this->belongsTo(PhotoAlbum::class, 'id', 'album_id');
    }

    /**
     * The user who uploaded this photo.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
