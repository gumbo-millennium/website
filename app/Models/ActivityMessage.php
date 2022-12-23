<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\States\Enrollment\State;
use DomainException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\LazyCollection;

/**
 * App\Models\ActivityMessage.
 *
 * @property int $id
 * @property int $activity_id
 * @property null|int $sender_id
 * @property string $target_audience
 * @property string $subject
 * @property string $body
 * @property null|int $receipients
 * @property null|\Illuminate\Support\Carbon $created_at
 * @property null|\Illuminate\Support\Carbon $updated_at
 * @property null|\Illuminate\Support\Carbon $sent_at
 * @property-read \App\Models\Activity $activity
 * @property-read null|\App\Models\User $sender
 * @method static Builder|ActivityMessage newModelQuery()
 * @method static Builder|ActivityMessage newQuery()
 * @method static Builder|ActivityMessage query()
 * @method static Builder|ActivityMessage unsent()
 * @mixin \Eloquent
 * @property \Illuminate\Support\Carbon|null $scheduled_at
 * @property-read bool $has_tickets
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Ticket[] $tickets
 * @method static Builder|ActivityMessage scheduledToSend()
 */
class ActivityMessage extends Model
{
    public const AUDIENCE_ANY = 'any';

    public const AUDIENCE_CONFIRMED = 'confirmed';

    public const AUDIENCE_PENDING = 'pending';

    public const VALID_AUDIENCES = [
        self::AUDIENCE_ANY,
        self::AUDIENCE_CONFIRMED,
        self::AUDIENCE_PENDING,
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'recipients' => 'int',
        'scheduled_at' => 'datetime',
        'sent_at' => 'datetime',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
        'user_id',
        'recipients',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'activity_id',
        'sender_id',
        'target_audience',
        'subject',
        'body',
    ];

    protected static function boot()
    {
        parent::boot();

        static::saving(static fn (self $message) => throw_unless(
            in_array($message->target_audience, self::VALID_AUDIENCES, true),
            DomainException::class,
            "Target audience [{$message->target_audience}] is invalid.",
        ));
    }

    public function activity(): BelongsTo
    {
        return $this->belongsTo(Activity::class);
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function tickets(): BelongsToMany
    {
        return $this->belongsToMany(Ticket::class);
    }

    public function getHasTicketsAttribute(): bool
    {
        return $this->tickets()->exists();
    }

    public function scopeUnsent(Builder $query): Builder
    {
        return $query->whereNull('sent_at');
    }

    public function scopeScheduledToSend(Builder $query): Builder
    {
        return $query->where(fn (Builder $query) => $query->whereNull('scheduled_at')->orWhere('scheduled_at', '<=', Date::now()));
    }

    public function getEnrollmentsCursor(): LazyCollection
    {
        $states = [];
        if ($this->target_audience === self::AUDIENCE_ANY || $this->target_audience === self::AUDIENCE_PENDING) {
            $states = array_merge($states, State::PENDING_STATES);
        }

        if ($this->target_audience === self::AUDIENCE_ANY || $this->target_audience === self::AUDIENCE_CONFIRMED) {
            $states = array_merge($states, State::CONFIRMED_STATES);
        }

        return $this->activity
            ->enrollments()
            ->whereState('state', $states)
            ->with('user')
            ->cursor();
    }
}
