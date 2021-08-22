<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Date;

/**
 * App\Models\DataExport
 *
 * @property int $id
 * @property int $user_id
 * @property string|null $path
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $completed_at
 * @property \Illuminate\Support\Carbon $expires_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|DataExport newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DataExport newQuery()
 * @method static \Illuminate\Database\Query\Builder|DataExport onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|DataExport query()
 * @method static \Illuminate\Database\Query\Builder|DataExport withTrashed()
 * @method static \Illuminate\Database\Query\Builder|DataExport withoutTrashed()
 * @mixin \Eloquent
 */
class DataExport extends Model
{
    use SoftDeletes;

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'completed_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public static function booted()
    {
        self::creating(function (self $dataExport) {
            $dataExport->expires_at = Date::now()->addDays(14);
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
