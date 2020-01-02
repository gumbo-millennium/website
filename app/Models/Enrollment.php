<?php

namespace App\Models;

use App\Models\States\Enrollment\Cancelled as CancelledState;
use App\Models\States\Enrollment\Confirmed as ConfirmedState;
use App\Models\States\Enrollment\Created as CreatedState;
use App\Models\States\Enrollment\Paid as PaidState;
use App\Models\States\Enrollment\Seeded as SeededState;
use App\Models\States\Enrollment\State as EnrollmentState;
use AustinHeap\Database\Encryption\Traits\HasEncryptedAttributes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\ModelStates\HasStates;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * A user enrollment for an activity. Optionally has payments.
 *
 * @property \App\Models\States\Enrollment\State $state
 */
class Enrollment extends UuidModel
{
    use HasEncryptedAttributes;
    use HasStates;
    use SoftDeletes;

    public const USER_TYPE_MEMBER = 'member';
    public const USER_TYPE_GUEST = 'guest';

    /**
     * @inheritDoc
     */
    protected $encrypted = [
        'data',
    ];

    /**
     * @inheritDoc
     */
    protected $casts = [
        'data' => 'collection',
        'paid' => 'bool'
    ];

    /**
     * @inheritDoc
     */
    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
        'expire',
    ];

    /**
     * Finds the active enrollment for this activity
     *
     * @param User $user
     * @param Activity $activity
     * @return Enrollment|null
     */
    public static function findActive(User $user, Activity $activity): ?Enrollment
    {
        return self::query()
            ->withoutTrashed()
            ->whereUserId($user->id)
            ->whereActivityId($activity->id)
            ->with(['activity'])
            ->first();
    }

    /**
     * Finds the active enrollment for this activity, or throws a 404 HTTP exception
     *
     * @param User $user
     * @param Activity $activity
     * @return Enrollment
     * @throws NotFoundHttpException if there is no enrollment present
     */
    public static function findActiveOrFail(User $user, Activity $activity): Enrollment
    {
        $result = self::findActive($user, $activity);
        if ($result) {
            return $result;
        }
        throw new NotFoundHttpException();
    }

    /**
     * Register the states an enrollment can have
     *
     * @return void
     */
    protected function registerStates(): void
    {
        // Register enrollment state
        $this
            ->addState('state', EnrollmentState::class)

            // Default to Created
            ->default(CreatedState::class)

            // Create → Seeded
            ->allowTransition(CreatedState::class, SeededState::class)

            // Seeded → Confirmed
            ->allowTransition(SeededState::class, ConfirmedState::class)

            // Seeded, Confirmed → Paid
            ->allowTransition([SeededState::class, ConfirmedState::class], PaidState::class)

            // Created, Seeded, Confirmed, Paid → Cancelled
            ->allowTransition(
                [CreatedState::class, SeededState::class, ConfirmedState::class, PaidState::class],
                CancelledState::class
            );
    }

    /**
     * An enrollment can have multiple payments (in case one failed, for example)
     *
     * @return HasMany
     */
    public function payments(): Relation
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * The user this enrollment belongs to
     *
     * @return BelongsTo
     */
    public function user(): Relation
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The activity this enrollment belongs to
     *
     * @return BelongsTo
     */
    public function activity(): Relation
    {
        return $this->belongsTo(Activity::class);
    }

    /**
     * Returns the form, but for testing purposes now returns null
     *
     * @return null
     */
    public function getFormAttribute(): ?string
    {
        return null;
    }

    /**
     * Returns true if the state is stable and will not auto-delete
     *
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getIsStableAttribute(): bool
    {
        return $this->state instanceof ConfirmedState;
    }
}
