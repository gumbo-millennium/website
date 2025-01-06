<?php

declare(strict_types=1);

namespace App\Jobs\User;

use App\Enums\EnrollmentCancellationReason;
use App\Jobs\Enrollments\CancelEnrollmentJob;
use App\Mail\AccountDeletedMail;
use App\Models\Enrollment;
use App\Models\States\Enrollment\Cancelled;
use App\Models\States\Enrollment\State;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use RuntimeException;
use Throwable;

class DeleteUserJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    private const NON_DELETABLE_PERMISSIONS = [
        'super-admin',
        'deny-delete',
    ];

    /**
     * Create a new job instance.
     */
    public function __construct(private User $user, private bool $force = false)
    {
        //
    }

    /**
     * Execute the job.
     * @throws Throwable If the rollback fails too
     */
    public function handle(): void
    {
        $user = $this->user;

        if (! $this->canBeDeleted()) {
            $this->fail(sprintf('User %s (%d) cannot be deleted', $user->name, $user->id));
        }

        try {
            DB::beginTransaction();

            $this->removeEnrollments($user);

            $this->deleteFileAssociations($user);

            Mail::to($user)
                ->send(new AccountDeletedMail($user));

            $user->forceDelete();

            DB::commit();
        } catch (Throwable $exception) {
            DB::rollBack();

            $this->fail(new RuntimeException(
                message: sprintf('Failed to delete user %s (%d): %s', $user->name, $user->id, $exception->getMessage()),
                previous: $exception,
            ));
        }
    }

    /**
     * Checks if all preconditions are met to delete this account.
     */
    public function canBeDeleted(): bool
    {
        $user = $this->user;

        if (! $user->exists) {
            return false;
        }

        if ($this->user->hasAnyPermission(self::NON_DELETABLE_PERMISSIONS)) {
            return false;
        }

        $hasActiveEnrollments = Enrollment::query()
            ->whereHas('user', fn ($q) => $q->where('id', $user->id))
            ->whereHas('activity', fn ($q) => $q->where('end_date', '>', Date::today()))
            ->whereState('state', [State::CONFIRMED_STATES])
            ->exists();

        if ($hasActiveEnrollments && ! $this->force) {
            return false;
        }

        return true;
    }

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
    private function deleteFileAssociations(User $user): void
    {
        // Unlink all files from this user
        $fileKeyName = $user->files()->getForeignKeyName();
        $user->files()->update([$fileKeyName => null]);

        // Unlink all downloads, but keep the IP on record
        $downloadsKeyName = $user->downloads()->getForeignKeyName();
        $user->downloads()->update([$downloadsKeyName => null]);
    }
}
