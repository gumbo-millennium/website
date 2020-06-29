<?php

declare(strict_types=1);

namespace App\Models;

use App\Jobs\Stripe\CreateInvoiceJob;
use App\Models\States\Enrollment\Cancelled as CancelledState;
use App\Models\States\Enrollment\Confirmed as ConfirmedState;
use App\Models\States\Enrollment\Created as CreatedState;
use App\Models\States\Enrollment\Paid as PaidState;
use App\Models\States\Enrollment\Refunded as RefundedState;
use App\Models\States\Enrollment\Seeded as SeededState;
use App\Models\States\Enrollment\State as EnrollmentState;
use AustinHeap\Database\Encryption\Traits\HasEncryptedAttributes;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\ModelStates\HasStates;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * A user enrollment for an activity. Optionally has payments.
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
     * Creates a proper enrollment
     * @param User $user
     * @param Activity $activity
     * @return Enrollment
     */
    public static function enrollUser(User $user, Activity $activity): Enrollment
    {
        // Create new enrollment
        $enrollment = new static();

        // Assign activity and user
        $enrollment->activity()->associate($activity);
        $enrollment->user()->associate($user);

        // Determine price with and without transfer cost
        $enrollment->price = $activity->price;
        $enrollment->total_price = $activity->total_price;
        if ($user->is_member && $activity->discounts_available !== 0 && $activity->member_discount !== null) {
            logger()->info('Applying member discount {discount}', ['discount' => $activity->member_discount]);
            $enrollment->price = $activity->discount_price;
            $enrollment->total_price = $activity->total_discount_price;
        }

        // Set to null if the price is empty
        if (!is_int($enrollment->price) || $enrollment->price <= 0) {
            logger()->info('Price empty, wiping it.');
            $enrollment->price = null;
            $enrollment->total_price = null;
        }

        // Debug
        $rawPrice = $enrollment->price;
        $price = $enrollment->total_price;
        logger()->debug(
            'Assigned enrollment price of {price} ({rawPrice}).',
            compact('user', 'activity', 'rawPrice', 'price')
        );

        // Save the enrollment
        $enrollment->save();

        // Debug
        logger()->info(
            'Enrolled user {user} on {activity}. ID is {enrollment-id}.',
            [
                'user' => $user,
                'activity' => $activity,
                'enrollment' => $enrollment,
                'enrollment-id' => $enrollment->id,
            ]
        );

        // Check if the enrollment is paid
        if ($enrollment->total_price) {
            // Dispatch a job to create a payment intent and invoice
            CreateInvoiceJob::dispatch($enrollment);
        }

        // Return it
        return $enrollment;
    }

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
     * An enrollment can have multiple payments (in case one failed, for example)
     * @return HasMany
     */
    public function payments(): Relation
    {
        return $this->hasMany(Payment::class);
    }

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
