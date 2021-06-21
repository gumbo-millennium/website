<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\EnrollmentServiceContract;
use App\Jobs\Stripe\CreateInvoiceJob;
use App\Jobs\Stripe\VoidInvoice;
use App\Models\Activity;
use App\Models\Enrollment;
use App\Models\States\Enrollment\Confirmed;
use App\Models\States\Enrollment\Paid;
use App\Models\States\Enrollment\Seeded;
use App\Models\States\Enrollment\State;
use App\Models\User;
use App\Notifications\EnrollmentTransferred;
use Illuminate\Contracts\Cache\Lock;
use Illuminate\Contracts\Cache\LockProvider;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
use LogicException;

class EnrollmentService implements EnrollmentServiceContract
{
    /**
     * @var CacheRepository|LockProvider
     */
    private CacheRepository $cache;

    public function __construct(CacheRepository $cache)
    {
        $this->cache = $cache;
    }

    /**
     * Returns true if the cache can be locked.
     */
    public function useLocks(): bool
    {
        return $this->cache instanceof LockProvider;
    }

    /**
     * @inheritdoc
     */
    public function getLock(Activity $activity): Lock
    {
        // Can't lock, return null
        if (! $this->useLocks()) {
            throw new LogicException('This service does not use locks');
        }

        // Sanity
        \assert($this->cache instanceof LockProvider);

        // Gets the lock
        $lock = $this->cache->lock("enroll.locking.{$activity->id}", 30);
        \assert($lock instanceof Lock);

        // Return it
        return $lock;
    }

    /**
     * @inheritdoc
     */
    public function canEnroll(Activity $activity, ?User $user): bool
    {
        // Check if open and not in the past
        if (! $activity->enrollment_open || $activity->end_date < now()) {
            return false;
        }

        // Check if there is still room
        if ($activity->seats !== null && $activity->available_seats <= 0) {
            return false;
        }

        // Check permissions
        if (! $user->can('enroll', $activity)) {
            return false;
        }

        // Check if no existing enrollment exists
        $current = Enrollment::findActive($user, $activity);
        if ($current !== null) {
            return false;
        }

        // All clear
        return true;
    }

    /**
     * Enrolls a user, /DOES NOT PERFORM CHECKS.
     */
    public function createEnrollment(Activity $activity, User $user): Enrollment
    {
        // Create new enrollment
        $enrollment = new Enrollment();

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
            $enrollment->user_type = 'member';
        }

        // Set to null if the price is empty
        if (! is_int($enrollment->price) || $enrollment->price <= 0) {
            logger()->info('Price empty, wiping it.');
            $enrollment->price = null;
            $enrollment->total_price = null;
        }

        // Debug
        $rawPrice = $enrollment->price;
        $price = $enrollment->total_price;
        logger()->debug(
            'Assigned enrollment price of {price} ({rawPrice}).',
            compact('user', 'activity', 'rawPrice', 'price'),
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
            ],
        );

        // Create invoice if the event is paid
        if ($enrollment->total_price) {
            // Dispatch a job to create a payment intent and invoice
            CreateInvoiceJob::dispatch($enrollment);
        }

        // Return it
        return $enrollment;
    }

    /**
     * Returns if the given enrollment can advance to the given state. If it's already on
     * or past said state, it should always return false.
     */
    public function canAdvanceTo(Enrollment $enrollment, string $wantedState): bool
    {
        if (! is_a($wantedState, State::class, true)) {
            throw new InvalidArgumentException("Requested state [${wantedState}], but it\\'s invalid.");
        }

        if (is_a($enrollment->state, $wantedState)) {
            return false;
        }

        $wantedStateName = (new $wantedState($enrollment))->name;
        if (! in_array($wantedStateName, $enrollment->state->transitionableStates(), true)) {
            return false;
        }

        if ($wantedState === Seeded::class) {
            return $enrollment->form !== null
                || $enrollment->activity->form === null;
        }

        if ($wantedState === Confirmed::class) {
            return ! ($enrollment->price > 0);
        }

        return false;
    }

    /**
     * Transitions states where possible.
     */
    public function advanceEnrollment(Activity $activity, Enrollment &$enrollment): void
    {
        if ($this->canAdvanceTo($enrollment, Seeded::class)) {
            Log::notice('Transitioning {enrollment} to seeded.', [
                'enrollment' => $enrollment,
            ]);

            $enrollment->state->transitionTo(Seeded::class);
            $enrollment->save();
        }

        if ($this->canAdvanceTo($enrollment, Confirmed::class)) {
            Log::notice('Transitioning {enrollment} to confirmed.', [
                'enrollment' => $enrollment,
            ]);

            $enrollment->state->transitionTo(Confirmed::class);
            $enrollment->save();
        }

        Log::info('Not transitioning {enrollment} further.', [
            'enrollment' => $enrollment,
        ]);
    }

    /**
     * Transfers an enrollment to the new user, sending proper mails and
     * invoicing jobs.
     */
    public function transferEnrollment(Enrollment $enrollment, User $reciever): Enrollment
    {
        // Get current
        $giver = $enrollment->user;
        \assert($giver instanceof User);

        // Sanity check
        if ($reciever->is($giver)) {
            throw new LogicException('Cannot transfer an enrollment to the same user');
        }

        // Transfer enrollment
        $enrollment->user()->associate($reciever);

        // Check expire, making sure it's at least 2 days
        if (! $enrollment->state->isStable()) {
            $enrollment->expire = max($enrollment->expire, now()->addDays(2));
        }

        // Make sure transfer token is unset
        $enrollment->transfer_secret = null;

        // Save changes
        $enrollment->save();

        // Send mails
        $giver->notify(new EnrollmentTransferred($enrollment, $giver));
        $reciever->notify(new EnrollmentTransferred($enrollment, $giver));

        // If not yet paid, make a new invoice
        if (! $enrollment->state instanceof Paid && $enrollment->price > 0) {
            VoidInvoice::withChain([
                new CreateInvoiceJob($enrollment),
            ])->dispatch($enrollment);
        }

        // Return it
        return $enrollment;
    }
}
