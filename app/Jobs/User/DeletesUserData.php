<?php

declare(strict_types=1);

namespace App\Jobs\User;

use App\Enums\EnrollmentCancellationReason;
use App\Jobs\Enrollments\CancelEnrollmentJob;
use App\Models\States\Enrollment\Cancelled;
use App\Models\States\Enrollment\State;
use App\Models\User;
use Illuminate\Support\Facades\Date;

trait DeletesUserData
{
    /**
     * Removes enrollments for this user, and cancels any cancellable ones in the future.
     */
    private function removeEnrollments(User $user): void
    {
        $pendingEnrollments = $user->enrollments()
            ->whereHas('activity', fn ($query) => $query->where('end_date', '>', Date::today()))
            ->whereNotState('state', State::CANCELLED_STATES)
            ->get();

        foreach ($pendingEnrollments as $enrollment) {
            CancelEnrollmentJob::dispatchSync($enrollment, EnrollmentCancellationReason::DELETION);
        }

        $user->enrollments()
            ->update([
                'state' => Cancelled::$name,
                'user_id' => null,
            ]);
    }

    private function removeShopOrders(User $user): void
    {
        $user->orders()->update([
            $user->orders()->getForeignKeyName() => null,
        ]);
    }

    /**
     * Deletes associations of a file, but not the files themselves.
     * @throws Throwable
     */
    private function removeFileAssociations(User $user): void
    {
        // Unlink all files from this user
        $fileKeyName = $user->files()->getForeignKeyName();
        $user->files()->update([$fileKeyName => null]);

        // Unlink all downloads, but keep the IP on record
        $downloadsKeyName = $user->downloads()->getForeignKeyName();
        $user->downloads()->update([$downloadsKeyName => null]);
    }
}
