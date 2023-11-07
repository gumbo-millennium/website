<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;

/**
 * App\Models\Ticket.
 *
 * @property int $id
 * @property null|int $activity_id
 * @property string $title
 * @property null|string $description
 * @property null|int $price
 * @property null|int $quantity
 * @property bool $is_public
 * @property null|\Illuminate\Support\Carbon $available_from
 * @property null|\Illuminate\Support\Carbon $available_until
 * @property null|\Illuminate\Support\Carbon $created_at
 * @property null|\Illuminate\Support\Carbon $updated_at
 * @property null|string $deleted_at
 * @property-read null|\App\Models\Activity $activity
 * @property-read \App\Models\Enrollment[]|\Illuminate\Database\Eloquent\Collection $enrollments
 * @property-read string $available_range
 * @property-read bool $is_being_sold
 * @property-read bool $members_only
 * @property-read null|int $quantity_available
 * @property-read int $quantity_sold
 * @property-read null|int $total_price
 * @method static \Database\Factories\TicketFactory factory(...$parameters)
 * @method static \Illuminate\Database\Eloquent\Builder|Ticket newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Ticket newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Ticket query()
 * @mixin \Eloquent
 */
class Ticket extends Model
{
    use HasFactory;
    use SoftDeletes;

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'price' => 'int',
        'quantity' => 'int',

        'is_public' => 'bool',

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
        'is_public',
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
        if ($this->is_public === false) {
            return true;
        }

        // If a ticket is public but the activity is members only, the ticket is also members only
        return $this->activity && ! $this->activity->is_public;
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
        return $this->enrollments()
            ->active()
            ->count();
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

    public function getAvailableRangeAttribute(): string
    {
        if ($this->available_from && $this->available_from > Date::now()) {
            return __('Available from :date', [
                'date' => $this->available_from->isoFormat('ddd DD MMM, HH:mm'),
            ]);
        }

        if ($this->available_until) {
            return __('Available until :date', [
                'date' => $this->available_until->isoFormat('ddd DD MMM, HH:mm'),
            ]);
        }

        return __('Freely available');
    }

    public function isAvailableFor(?User $user): bool
    {
        return $this->is_being_sold
            && $this->quantity_available !== 0
            && $this->activity->available_seats !== 0
            && (! $this->members_only || optional($user)->is_member);
    }

    /**
     * Scope a query to only include tickets that are on sale right now, and have not
     * sold out yet.
     */
    public function scopeAvailableNow(Builder $query): void
    {
        // Must have started selling
        $query->where(
            fn ($subquery) => $subquery
                ->whereNull('available_from')
                ->orWhere('available_from', '<=', Date::now()),
        );

        // Must not have stopped selling
        $query->where(
            fn ($subquery) => $subquery
                ->whereNull('available_until')
                ->orWhere('available_until', '>=', Date::now()),
        );

        // Must still have spots available
        $query->where(
            fn ($sub) => $sub
                ->whereNull('quantity')
                ->orWhereHas('enrollments', fn ($sub) => $sub->active(), '<', DB::raw('quantity')),
        );
    }

    /**
     * Scope a query to only include tickets that are on sale right now, and have not
     * sold out yet, and are available for the given user.
     */
    public function scopeAvailableNowFor(Builder $query, ?User $user)
    {
        $query->availableNow();
        $query->unless($user?->is_member, fn ($sub) => $sub->where('is_public', '=', true));
    }
}
