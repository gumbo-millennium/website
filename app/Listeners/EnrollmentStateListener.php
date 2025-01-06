<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Models\Enrollment;
use App\Models\States\Enrollment as States;
use App\Notifications\EnrollmentCancelled;
use App\Notifications\EnrollmentConfirmed;
use App\Notifications\EnrollmentPaid;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Date;
use Spatie\ModelStates\Events\StateChanged;

class EnrollmentStateListener
{
    use InteractsWithQueue;

    private static bool $silenced = false;

    public static function setSilenced(bool $silenced): void
    {
        self::$silenced = $silenced;
    }

    /**
     * Handle the event.
     *
     * @return void
     */
    public function handle(StateChanged $event)
    {
        // Exit early if silenced.
        if (self::$silenced) {
            return;
        }

        // Don't act on enrollments
        if (! $event->model instanceof Enrollment) {
            return;
        }

        // Get shorthand
        $enrollment = $event->model;
        $finalState = $enrollment->state;
        $user = $enrollment->user;

        // Handle payment completion
        if ($finalState instanceof States\Paid) {
            $user->notify(new EnrollmentPaid($enrollment));

            return;
        }

        // Prevent queue racing
        $cacheKey = "race-prevention.enroll.{$enrollment->id}.{$finalState}";
        if (Cache::has($cacheKey)) {
            return;
        }
        Cache::put($cacheKey, 1, Date::now()->addMinutes(5));

        // Handle non-payment confirmation (Paid extends Confirmed)
        if ($finalState instanceof States\Confirmed) {
            $user->notify(new EnrollmentConfirmed(($enrollment)));

            return;
        }

        // Handle cancellation
        if ($finalState instanceof States\Cancelled) {
            $user->notify(new EnrollmentCancelled($enrollment));

            return;
        }
    }
}
