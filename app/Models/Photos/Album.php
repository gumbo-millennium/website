<?php

declare(strict_types=1);

namespace App\Models\Photos;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * App\Models\Photos\Album.
 *
 * @property int $id
 * @property null|int $user_id
 * @property string $name
 * @property null|string $description
 * @property string $visibility
 * @property null|\Illuminate\Support\Carbon $created_at
 * @property null|\Illuminate\Support\Carbon $updated_at
 * @property string $published_at
 * @property-read \App\Models\Photos\Photo[]|\Illuminate\Database\Eloquent\Collection $photos
 * @property-read null|User $user
 * @method static \Illuminate\Database\Eloquent\Builder|Album newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Album newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Album query()
 * @mixin \Eloquent
 */
class Album extends Model
{
    public function photos(): HasMany
    {
        return $this->hasMany(Photo::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
