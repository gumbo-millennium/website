<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Models\Enrollment;
use App\Models\States\Enrollment as States;
use App\Notifications\EnrollmentCancelled;
use App\Notifications\EnrollmentConfirmed;
use App\Notifications\EnrollmentPaid;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\RateLimiter;
use Spatie\ModelStates\Events\StateChanged;

class EnrollmentStateListener implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Handle the event.
     *
     * @return void
     */
    public function handle(StateChanged $event)
    {
        // Don't act on enrollments
        if (! $event->model instanceof Enrollment) {
            return;
        }

        // Get shorthand
        $initialState = $event->initialState;
        $finalState = $event->finalState;

        $enrollment = $event->model;
        $finalState ??= $enrollment->state;

        $user = $enrollment->user;

        // Don't act on non-changes
        if ($initialState && $finalState && $initialState::class === $finalState::class) {
            return;
        }

        // Use the rate limiter to prevent notification flooding
        $cacheKey = sprintf('racing:enrollments:%d:%s', $enrollment->id, $finalState::class);
        if (RateLimiter::tooManyAttempts($cacheKey, 1)) {
            return;
        }

        RateLimiter::hit($cacheKey);

        // Handle payment completion
        if ($finalState instanceof States\Paid) {
            $user->notify(new EnrollmentPaid($enrollment));

            return;
        }

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
