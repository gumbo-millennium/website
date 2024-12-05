<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\States\Enrollment\State;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\LazyCollection;

/**
 * App\Models\ActivityMessage.
 *
 * @property int $id
 * @property int $activity_id
 * @property null|int $sender_id
 * @property bool $include_pending
 * @property string $subject
 * @property string $body
 * @property null|int $recipients
 * @property null|\Illuminate\Support\Carbon $created_at
 * @property null|\Illuminate\Support\Carbon $updated_at
 * @property null|\Illuminate\Support\Carbon $deleted_at
 * @property null|\Illuminate\Support\Carbon $scheduled_at
 * @property null|\Illuminate\Support\Carbon $sent_at
 * @property-read \App\Models\Activity $activity
 * @property-read int $expected_recipients
 * @property-read bool $has_tickets
 * @property-read null|\App\Models\User $sender
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Ticket> $tickets
 * @method static \Database\Factories\ActivityMessageFactory factory($count = null, $state = [])
 * @method static Builder|ActivityMessage forEnrollment(\App\Models\Enrollment $enrollment)
 * @method static Builder|ActivityMessage newModelQuery()
 * @method static Builder|ActivityMessage newQuery()
 * @method static Builder|ActivityMessage onlyTrashed()
 * @method static Builder|ActivityMessage query()
 * @method static Builder|ActivityMessage sent()
 * @method static Builder|ActivityMessage shouldBeSent()
 * @method static Builder|ActivityMessage unsent()
 * @method static Builder|ActivityMessage withTrashed()
 * @method static Builder|ActivityMessage withoutTrashed()
 * @mixin \Eloquent
 */
class ActivityMessage extends Model
{
    use HasFactory;
    use SoftDeletes;

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'include_pending' => 'bool',
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
        'include_pending',
        'scheduled_at',
        'subject',
        'body',
    ];

    protected static function boot()
    {
        parent::boot();
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

    public function scopeSent(Builder $query): Builder
    {
        return $query->whereNotNull('sent_at');
    }

    public function scopeShouldBeSent(Builder $query): Builder
    {
        return $query->where(fn (Builder $query) => $query->whereNull('scheduled_at')->orWhere('scheduled_at', '<=', Date::now()));
    }

    /**
     * Returns a lazy collection of the enrollments this message will be sent to.
     * @return \App\Models\Enrollment[]|LazyCollection
     */
    public function getEnrollmentsCursor(): LazyCollection
    {
        $states = [
            ...State::CONFIRMED_STATES,
            ...($this->include_pending ? State::PENDING_STATES : []),
        ];

        return $this->activity
            ->enrollments()
            ->whereState('state', $states)
            ->when(
                $this->tickets->isNotEmpty(),
                fn ($query) => $query->whereHas('ticket', fn ($query) => $query->whereIn('id', $this->tickets->pluck('id'))),
            )
            ->with('user')
            ->cursor();
    }

    /**
     * Scopes the query to match the given enrollment.
     */
    public function scopeForEnrollment(Builder $query, Enrollment $enrollment): void
    {
        // Restrict to activity
        $query->whereHas('activity', fn () => $query->where('id', $enrollment->activity->id));

        // Restrict to ticket if applicable
        $query->where(
            fn ($query) => $query
                ->doesntHave('tickets')
                ->orWhereHas('tickets', fn () => $query->where('id', $enrollment->ticket->id)),
        );
    }

    /**
     * Returns the number of users that will likely recieve this message.
     */
    public function getExpectedRecipientsAttribute(): int
    {
        if ($this->send_at !== null) {
            return $this->recipients;
        }

        return $this->getEnrollmentsCursor()->count();
    }
}
