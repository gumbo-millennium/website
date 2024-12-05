<?php

declare(strict_types=1);

namespace App\Models\Gallery;

use App\Enums\PhotoReportResolution;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\Gallery\PhotoReport.
 *
 * @property int $id
 * @property int $photo_id
 * @property null|int $user_id
 * @property string $reason
 * @property PhotoReportResolution $resolution
 * @property null|\Illuminate\Support\Carbon $created_at
 * @property null|\Illuminate\Support\Carbon $updated_at
 * @property null|\Illuminate\Support\Carbon $resolved_at
 * @property-read \App\Models\Gallery\Photo $photo
 * @property-read null|User $user
 * @method static \Database\Factories\Gallery\PhotoReportFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder|PhotoReport newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PhotoReport newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PhotoReport query()
 * @mixin \Eloquent
 */
class PhotoReport extends Model
{
    use HasFactory;

    /**
     * The model's attributes.
     *
     * @var array
     */
    protected $attributes = [
        'resolution' => PhotoReportResolution::Pending,
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'resolved_at' => 'datetime',
        'resolution' => PhotoReportResolution::class,
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'reason',
        'resolution',
    ];

    public function photo(): BelongsTo
    {
        return $this->belongsTo(Photo::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getIsResolvedProperty(): bool
    {
        return $this->resolution !== PhotoReportResolution::Pending;
    }
}
