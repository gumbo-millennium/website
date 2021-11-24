<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\EnrollmentServiceContract;
use App\Exceptions\EnrollmentFailedException;
use App\Jobs\Stripe\CreateInvoiceJob;
use App\Jobs\Stripe\VoidInvoice;
use App\Models\Activity;
use App\Models\Enrollment;
use App\Models\States\Enrollment as States;
use App\Models\Ticket;
use App\Models\User;
use App\Notifications\EnrollmentTransferred;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Date;
use LogicException;

class EnrollmentService implements EnrollmentServiceContract
{
    public function getEnrollment(Activity $activity): ?Enrollment
    {
        if (! $user = Auth::user()) {
            return null;
        }

        return $user
            ->enrollments()
            ->whereNotState('state', [
                States\Cancelled::class,
            ])
            ->whereHas('activity', fn ($query) => $query->where('id', $activity->id))
            ->first();
    }

    public function canEnroll(Activity $activity): bool
    {
        // Check if the user is already enrolled
        if ($this->getEnrollment($activity) !== null) {
            return false;
        }

        // Check if the activity has ended
        if ($activity->end_date < Date::now()) {
            return false;
        }

        // Check if there are any slots left
        if ($activity->available_seats === 0) {
            return false;
        }

        // Check if there are any tickets left that this user can buy
        $tickets = $this->findTicketsForActivity($activity);
        if (empty($tickets)) {
            return false;
        }

        // Should be fine at this point
        return true;
    }

    public function findTicketsForActivity(Activity $activity): array
    {
        $activity->loadMissing('tickets');

        $user = Auth::user();

        /** @var Ticket $ticket */
        return $activity->tickets
            ->filter(fn (Ticket $ticket) => (
                $ticket->is_being_sold
                    && $ticket->quantity_available !== 0
                    && (! $ticket->members_only || optional($user)->is_member)
            ))
            ->values()
            ->all();
    }

    public function createEnrollment(Activity $activity, Ticket $ticket): Enrollment
    {
        throw_unless($user = Auth::user(), new LogicException('There is no user logged in'));

        abort_if($this->getEnrollment($activity), new EnrollmentFailedException("You're already enrolled for this activity"));

        abort_unless($ticket->is_being_sold, new EnrollmentFailedException('This ticket is no longer being sold'));

        $ticket->refresh();

        abort_if($ticket->quantity_available === 0, new EnrollmentFailedException('This ticket is sold out'));

        $enrollment = $activity->enrollments()->make();
        $enrollment->ticket()->associate($ticket);
        $enrollment->user()->associate($user);

        $enrollment->price = $ticket->total_price;
        $enrollment->save();

        return $enrollment;
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

        // Check expire, making sure it's at least 12 hours
        if (! $enrollment->state->isStable()) {
            $enrollment->expire = max($enrollment->expire, now()->addHours(12));
        }

        // Make sure transfer token is unset
        $enrollment->transfer_secret = null;

        // Save changes
        $enrollment->save();

        // Send mails
        $giver->notify(new EnrollmentTransferred($enrollment, $giver));
        $reciever->notify(new EnrollmentTransferred($enrollment, $giver));

        // If not yet paid, make a new invoice
        if (! $enrollment->state instanceof States\Paid && $enrollment->price > 0) {
            VoidInvoice::withChain([
                new CreateInvoiceJob($enrollment),
            ])->dispatch($enrollment);
        }

        // Return it
        return $enrollment;
    }
}
