<?php

declare(strict_types=1);

namespace App\Observers;

use App\Jobs\GoogleWallet as GoogleWalletJobs;
use App\Models\Activity;
use App\Services\Google\WalletService;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Date;

/**
 * Ensures values on the activity model are up to snuff.
 */
class ActivityObserver
{
    public function __construct(private WalletService $walletService)
    {
        //
    }

    /**
     * Validates values of an Activity. Has a high complexity but isn't run too often.
     *
     * @param \App\App\Models\Activity $activity
     * @return void
     */
    public function saving(Activity $activity)
    {
        // Normalize seats
        // Make sure the seats aren't below or equal to zero, it should be null
        if ($activity->seats !== null && $activity->seats <= 0) {
            $activity->seats = null;
        }

        // Normalize enrollment dates
        $notOpenFree = $activity->seats !== null || ! $activity->is_free;

        // Cap enrollment end on the end date of the activity end
        if ($activity->enrollment_end > $activity->end_date) {
            $activity->enrollment_end = $activity->end_date;
        }

        // Ensure a start date exists if the event is not open and free
        if ($notOpenFree && $activity->enrollment_start === null) {
            $activity->enrollment_start = Date::today()->subDays(7);
        }

        // Ensure an end date is set if a start date is too
        if ($activity->enrollment_start !== null && $activity->enrollment_end === null) {
            $activity->enrollment_end = $activity->start_date;
        }
    }

    /**
     * Make sure a Google Wallet EventTicketClass is created after the activity is created.
     */
    public function created(Activity $activity): void
    {
        if ($this->walletService->isEnabled()) {
            GoogleWalletJobs\CreateEventTicketClassJob::dispatch($activity);
        }
    }

    /**
     * Make sure the Google Wallet EventTicketClass for this activity is updated after the activity is updated.
     */
    public function updated(Activity $activity): void
    {
        if ($this->walletService->isEnabled()) {
            GoogleWalletJobs\UpdateEventTicketClassJob::dispatch($activity);
        }
    }
}
