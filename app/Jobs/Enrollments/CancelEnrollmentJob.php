<?php

declare(strict_types=1);

namespace App\Jobs\Enrollments;

use App\Enums\EnrollmentCancellationReason;
use App\Listeners\EnrollmentStateListener;
use App\Models\Enrollment;
use App\Models\States\Enrollment as States;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use RuntimeException;

/**
 * @method static void dispatch(Enrollment $enrollment, null|EnrollmentCancellationReason $reason = null, bool $quiet = false)
 * @method static void dispatchSync(Enrollment $enrollment, null|EnrollmentCancellationReason $reason = null, bool $quiet = false)
 */
class CancelEnrollmentJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(
        private Enrollment $enrollment,
        private ?EnrollmentCancellationReason $reason = null,
        private bool $quiet = false,
    ) {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $enrollment = $this->enrollment;
        $adminIssued = $this->reason == EnrollmentCancellationReason::ADMIN;

        // Already cancelled, might be a race condition
        if ($enrollment->state instanceof States\Cancelled) {
            return;
        }

        // If the enrollment is paid, reject if not issued by an admin
        if ($enrollment->state instanceof States\Paid && ! $adminIssued) {
            $this->fail(new RuntimeException(
                'Cannot cancel a paid enrollment without admin permission',
            ));
        }

        // Store reason first
        $enrollment->deleted_reason = $this->reason ?? EnrollmentCancellationReason::USER_REQUEST;

        // Cancel the enrollment
        EnrollmentStateListener::setSilenced($this->quiet === true);
        $enrollment->state->transitionTo(States\Cancelled::class);

        // Done
        $enrollment->save();
    }
}
