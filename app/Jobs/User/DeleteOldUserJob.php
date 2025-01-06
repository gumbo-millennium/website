<?php

declare(strict_types=1);

namespace App\Jobs\User;

use App\Mail\Account\AccountDeletedMail;
use App\Models\Enrollment;
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

class DeleteOldUserJob implements ShouldQueue
{
    use DeletesUserData;
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    private const NON_DELETABLE_PERMISSIONS = [
        'super-admin',
        'deny-delete',
    ];

    private ?User $user = null;

    private bool $force = false;

    /**
     * Create a new job instance.
     */
    public function __construct(?User $user = null, bool $force = false)
    {
        $this->user = $user;
        $this->force = $force;
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

            $this->removeFileAssociations($user);

            $this->removeShopOrders($user);

            Mail::to($user)
                ->send(new AccountDeletedMail($user));

            $user->forceDelete();

            DB::commit();

            $this->user = null;
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
}
