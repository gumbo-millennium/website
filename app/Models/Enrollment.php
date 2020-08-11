<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\States\Enrollment\Cancelled as CancelledState;
use App\Models\States\Enrollment\Confirmed as ConfirmedState;
use App\Models\States\Enrollment\Created as CreatedState;
use App\Models\States\Enrollment\Paid as PaidState;
use App\Models\States\Enrollment\Refunded as RefundedState;
use App\Models\States\Enrollment\Seeded as SeededState;
use App\Models\States\Enrollment\State as EnrollmentState;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Eloquent\SoftDeletes;
use Roelofr\EncryptionCast\Casts\Compat\AustinHeapEncryptedAttribute as EncryptedAttribute;
use Spatie\ModelStates\HasStates;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * A user enrollment for an activity.
 * @property \App\Models\States\Enrollment\State $state
 */
class Enrollment extends UuidModel
{
    use HasStates;
    use SoftDeletes;

    public const USER_TYPE_MEMBER = 'member';
    public const USER_TYPE_GUEST = 'guest';

    /**
     * Finds the active enrollment for this activity
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
            ->whereNotState('state', [CancelledState::class, RefundedState::class])
            ->with(['activity'])
            ->first();
    }

    /**
     * Finds the active enrollment for this activity, or throws a 404 HTTP exception
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
     * @inheritDoc
     */
    protected $casts = [
        'data' => EncryptedAttribute::class . ':collection',
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
     * The user this enrollment belongs to
     * @return BelongsTo
     */
    public function user(): Relation
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The activity this enrollment belongs to
     * @return BelongsTo
     */
    public function activity(): Relation
    {
        return $this->belongsTo(Activity::class);
    }

    /**
     * Returns true if the state is stable and will not auto-delete
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getIsStableAttribute(): bool
    {
        return $this->state instanceof ConfirmedState;
    }

    /**
     * Returns if the enrollment is discounted.
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getIsDiscountedAttribute(): bool
    {
        return $this->price === $this->activity->discount_price;
    }

    /**
     * Returns state we want to go to, depending on Enrollment's own attributes.
     * Returns null if it can't figure it out.
     * @return App\Models\States\Enrollment\State|null
     */
    public function getWantedStateAttribute(): ?EnrollmentState
    {
        // First check for any transition
        $options = $this->state->transitionableStates();
        if (in_array(SeededState::$name, $options) && $this->activity->form) {
            return new SeededState($this);
        } elseif (in_array(PaidState::$name, $options) && $this->price) {
            return new PaidState($this);
        } elseif (in_array(ConfirmedState::$name, $options)) {
            return new ConfirmedState($this);
        }

        return null;
    }

    public function getRequiresPaymentAttribute(): bool
    {
        return $this->exists &&
            $this->total_price &&
            !($this->state instanceof CancelledState);
    }

    /**
     * Register the states an enrollment can have
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

            // Created, Seeded → Confirmed
            ->allowTransition([CreatedState::class, SeededState::class], ConfirmedState::class)

            // Created, Seeded, Confirmed → Paid
            ->allowTransition([CreatedState::class, SeededState::class, ConfirmedState::class], PaidState::class)

            // Created, Seeded, Confirmed, Paid → Cancelled
            ->allowTransition(
                [CreatedState::class, SeededState::class, ConfirmedState::class, PaidState::class],
                CancelledState::class
            )

            // Paid, Cancelled → Refunded
            ->allowTransition(
                [PaidState::class, CancelledState::class],
                RefundedState::class
            );
    }
}
