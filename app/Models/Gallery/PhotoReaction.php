<?php

declare(strict_types=1);

namespace App\Models\Gallery;

use App\Enums\PhotoReactionType;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\Gallery\PhotoReaction.
 *
 * @property int $id
 * @property int $photo_id
 * @property null|int $user_id
 * @property PhotoReactionType $reaction
 * @property-read \App\Models\Gallery\Photo $photo
 * @property-read null|User $user
 * @method static \Database\Factories\Gallery\PhotoReactionFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder|PhotoReaction newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PhotoReaction newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PhotoReaction query()
 * @mixin \Eloquent
 */
class PhotoReaction extends Model
{
    use HasFactory;

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'reaction' => PhotoReactionType::class,
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'reaction',
    ];

    public function photo(): BelongsTo
    {
        return $this->belongsTo(Photo::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
