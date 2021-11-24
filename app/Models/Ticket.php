<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\States\Enrollment as States;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Date;

/**
 * App\Models\Ticket.
 *
 * @property int $id
 * @property null|int $activity_id
 * @property string $title
 * @property null|string $description
 * @property null|int $price
 * @property null|int $quantity
 * @property bool $members_only
 * @property null|\Illuminate\Support\Carbon $available_from
 * @property null|\Illuminate\Support\Carbon $available_until
 * @property null|\Illuminate\Support\Carbon $created_at
 * @property null|\Illuminate\Support\Carbon $updated_at
 * @property null|string $deleted_at
 * @property-read null|\App\Models\Activity $activity
 * @property-read \App\Models\Enrollment[]|\Illuminate\Database\Eloquent\Collection $enrollments
 * @property-read bool $is_being_sold
 * @property-read null|int $quantity_available
 * @property-read int $quantity_sold
 * @property-read null|int $total_price
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

    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
    }

    public function getMembersOnlyAttribute(): bool
    {
        // Tickets can be members only
        if ($this->attributes['members_only'] === true) {
            return true;
        }

        // If a ticket is public but the activity is members only, the ticket is also members only
        return ! $this->activity->is_public;
    }

    public function getIsBeingSoldAttribute(): bool
    {
        return (
            ($this->available_from === null || $this->available_from < Date::now())
            && ($this->available_until === null || $this->available_until > Date::now())
        );
    }

    public function getQuantitySoldAttribute(): int
    {
        return $this->enrollments()->whereNotState('state', [
            States\Cancelled::class,
        ])->count();
    }

    public function getQuantityAvailableAttribute(): ?int
    {
        if ($this->quantity === null) {
            return null;
        }

        return $this->quantity - $this->quantity_sold;
    }

    public function getTotalPriceAttribute(): ?int
    {
        if (($price = $this->price) === null) {
            return null;
        }

        return $price + Config::get('gumbo.transfer-fee');
    }
}
