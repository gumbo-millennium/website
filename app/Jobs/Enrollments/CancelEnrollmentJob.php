<?php

declare(strict_types=1);

namespace App\Jobs\Enrollments;

use App\Enums\EnrollmentCancellationReason;
use App\Models\Enrollment;
use App\Models\States\Enrollment as States;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use RuntimeException;

class CancelEnrollmentJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    private Enrollment $enrollment;

    private bool $adminIssued;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Enrollment $enrollment, bool $adminIssued = false)
    {
        $this->enrollment = $enrollment;
        $this->adminIssued = $adminIssued;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $enrollment = $this->enrollment;
        $adminIssued = $this->adminIssued;

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
        $enrollment->deleted_reason = $adminIssued
            ? EnrollmentCancellationReason::ADMIN
            : EnrollmentCancellationReason::USER_REQUEST;

        // Cancel the enrollment
        $enrollment->transitionTo(States\Cancelled::class);

        // Done
        $enrollment->save();
    }
}
