<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\Ticket.
 *
 * @property int $id
 * @property null|int $activity_id
 * @property string $title
 * @property string $description
 * @property null|int $price
 * @property null|int $quantity
 * @property bool $members_only
 * @property null|\Illuminate\Support\Carbon $available_from
 * @property null|\Illuminate\Support\Carbon $available_until
 * @property null|\Illuminate\Support\Carbon $created_at
 * @property null|\Illuminate\Support\Carbon $updated_at
 * @property null|string $deleted_at
 * @property-read null|\App\Models\Activity $activity
 * @method static \Illuminate\Database\Eloquent\Builder|Ticket newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Ticket newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Ticket query()
 * @mixin \Eloquent
 */
class Ticket extends Model
{
    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'price' => 'int',
        'quantity' => 'int',

        'members_only' => 'bool',

        'available_from' => 'datetime',
        'available_until' => 'datetime',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'title',
        'description',
        'price',
        'quantity',
        'members_only',
        'available_from',
        'available_until',
    ];

    public function activity(): BelongsTo
    {
        return $this->belongsTo(Activity::class);
    }
}
