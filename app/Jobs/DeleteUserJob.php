<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

/**
 * Deletes data associated with a user.
 */
class DeleteUserJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * User to remove
     */
    protected User $user;

    /**
     * Create a new job instance.
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * Execute the deletion
     */
    public function handle(): void
    {
        // Delete filesystem actions
        $this->deleteFileAssociations($this->user);

        // Delete user
        $this->user->forceDelete();
    }

    /**
     * Deletes associations of a file, but not the files themselves
     */
    protected function deleteFileAssociations(User $user): void
    {
        // Unlink all files from this user
        DB::transaction(static function () use ($user): void {
            // Get property name
            $keyName = $user->files()->getForeignKeyName();

            // Set property to null
            $user->files()->update([$keyName => null]);
        });

        // Unlink all downloads, but keep the IP on record
        DB::transaction(static function () use ($user): void {
            // Get property name
            $keyName = $user->downloads()->getForeignPivotKeyName();

            // Set property to null
            $user->downloads()->update([$keyName => null]);
        });
    }
}
