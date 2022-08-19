<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\EnrollmentServiceContract;
use App\Exceptions\EnrollmentFailedException;
use App\Helpers\Str;
use App\Models\Activity;
use App\Models\Enrollment;
use App\Models\States\Enrollment as States;
use App\Models\Ticket;
use App\Models\User;
use App\Notifications\EnrollmentTransferred;
use Endroid\QrCode\Builder\Builder as QRBuilder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelHigh;
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\Database\QueryException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
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
        if ($tickets->isEmpty()) {
            return false;
        }

        // Should be fine at this point
        return true;
    }

    public function findTicketsForActivity(Activity $activity): Collection
    {
        $activity->loadMissing('tickets');

        $user = Auth::user();

        /** @var Ticket $ticket */
        return $activity->tickets
            ->filter->isAvailableFor($user)
            ->values();
    }

    public function createEnrollment(Activity $activity, Ticket $ticket): Enrollment
    {
        throw_unless($user = Auth::user(), new LogicException('There is no user logged in'));

        throw_if($this->getEnrollment($activity), new EnrollmentFailedException("You're already enrolled for this activity"));

        throw_unless($ticket->isAvailableFor($user), new EnrollmentFailedException('This ticket is not available'));

        $ticket->refresh();

        throw_if($ticket->quantity_available === 0, new EnrollmentFailedException('This ticket is sold out'));

        throw_if($ticket->activity->refresh()->available_seats === 0, new EnrollmentFailedException('This activity is sold out'));

        throw_unless($this->canEnroll($activity), new EnrollmentFailedException('You cannot enroll for this activity'));

        /** @var Enrollment $enrollment */
        $enrollment = $activity->enrollments()->make();
        $enrollment->ticket()->associate($ticket);
        $enrollment->user()->associate($user);

        // Assign price
        $enrollment->price = $ticket->price;
        $enrollment->total_price = $ticket->total_price;

        // Assign expiration
        $expirationPeriod = $user == null ? Config::get('gumbo.tickets.expiration.anonymous') : Config::get('gumbo.tickets.expiration.authenticated');
        $enrollment->expire = Date::now()->add($expirationPeriod);
        $enrollment->save();

        return $enrollment;
    }

    public function canTransfer(Enrollment $enrollment): bool
    {
        if ($enrollment->state instanceof States\Cancelled || $enrollment->trashed() || $enrollment->consumed()) {
            return false;
        }

        $activity = $enrollment->activity;
        if ($activity->start_date < Date::now()) {
            return false;
        }

        return true;
    }

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

        // Wipe form data if it's sensitive
        if ($enrollment->is_form_exportable === false) {
            $enrollment->form = [];
        }

        // Save changes
        $enrollment->save();

        // Send mails
        $giver->notify(new EnrollmentTransferred($enrollment, $giver));
        $reciever->notify(new EnrollmentTransferred($enrollment, $giver));

        // Return it
        return $enrollment;
    }

    /**
     * Generates a new unique ticket code for the enrollment.
     */
    public function updateTicketCode(Enrollment $enrollment): void
    {
        // Try to generate a new code 10 times
        for ($i = 0; $i < 10; $i++) {
            try {
                $enrollment->ticket_code = Str::upper(Str::random(8));
            } catch (QueryException $exception) {
                if (Str::contains(Str::lower($exception->getMessage()), 'unqiue')) {
                    continue;
                }

                throw $exception;
            }
        }
    }

    public function getTicketQrCode(Enrollment $enrollment, int $size = 400): string
    {
        return QRBuilder::create()
            ->writer(new PngWriter())
            ->encoding(new Encoding('ISO-8859-1')) // Not UTF-8 for highest compatibility
            ->errorCorrectionLevel(new ErrorCorrectionLevelHigh())
            ->size($size)
            ->margin(0)
            ->data(Str::ascii($enrollment->enrollment_code))
            ->build()
            ->getDataUri();
    }
}
