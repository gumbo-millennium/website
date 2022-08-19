<?php

declare(strict_types=1);

namespace App\Observers;

use App\Jobs\GoogleWallet as GoogleWalletJobs;
use App\Models\Enrollment;
use App\Models\States\Enrollment\State as EnrollmentState;
use App\Services\Google\WalletService;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Date;

/**
 * Listens for changes in enrollment elements. Sends users mails when they're
 * enrolled and unenrolled for events.
 */
class EnrollmentObserver
{
    public function __construct(private WalletService $walletService)
    {
        //
    }

    /**
     * Ensure an expire date is present if required.
     */
    public function saving(Enrollment $enrollment): void
    {
        // Don't alter enrollments after the event has started
        if ($enrollment->activity->start_date < Date::now()) {
            return;
        }

        // Check if the enrollment is stable
        $isStable = $enrollment->state instanceof EnrollmentState && $enrollment->state->isStable();

        // Wipe expiration if the enrollment entered a stable state
        if ($isStable) {
            if ($enrollment->expire !== null) {
                $enrollment->expire = null;
            }

            return;
        }

        // Expire enrollments in 1 hour, unless already set.
        $enrollment->expire ??= Date::now()->addHour();
    }

    /**
     * Make sure a Google Wallet EventTicketObject is created after an enrollment is created.
     */
    public function created(Enrollment $enrollment): void
    {
        if ($this->walletService->isEnabled()) {
            GoogleWalletJobs\CreateEventTicketObjectJob::dispatch($enrollment);
        }
    }

    /**
     * Make sure the Google Wallet EventTicketObject for this enrollment is updated after the activity is updated.
     */
    public function updated(Enrollment $enrollment): void
    {
        if ($this->walletService->isEnabled()) {
            GoogleWalletJobs\UpdateEventTicketObjectJob::dispatch($enrollment);
        }
    }
}
