<?php

namespace App\Jobs;

use App\Models\User;
use Corcel\Services\PasswordService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use App\Models\CorcelUser;

/**
 * Deletes data associated with a user.
 *
 * @author Roelof Roos <github@roelof.io>
 * @license MPL-2.0
 */
class DeleteUserJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * User to remove
     *
     * @var User
     */
    protected $user;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * Execute the deletion
     *
     * @return void
     */
    public function handle()
    {
        // Delete filesystem actions
        $this->deleteFileAssociations($this->user);

        // Delete WordPress account
        $this->deleteWordPress($this->user);

        // Delete user
        $this->user->forceDelete();

        // Return OK
        return $this->user->exists() === false;
    }

    /**
     * Deletes associations of a file, but not the files themselves
     *
     * @param User $user
     * @return void
     */
    protected function deleteFileAssociations(User $user): void
    {
        // Unlink all files from this user
        DB::transaction(function () use ($user) {
            // Get property name
            $keyName = $user->files()->getForeignKeyName();

            // Set property to null
            $user->files()->update([$keyName => null]);
        });

        // Unlink all downloads, but keep the IP on record
        DB::transaction(function () use ($user) {
            // Get property name
            $keyName = $user->downloads()->getForeignPivotKeyName();

            // Set property to null
            $user->downloads()->update([$keyName => null]);
        });
    }

    /**
     * Deletes the user's WordPress account, moving and deleting
     * assets as we go
     *
     * @param User $user
     * @return void
     */
    protected function deleteWordpress(User $user): void
    {
        // Get WordPress account, if any
        $wpUser = $user->wordpress;

        // No op if not found
        if (!$wpUser) {
            return;
        }

        // Get default user
        $defaultUser = $this->getDefaultWordpressUser();

        // Wrap in transaction, again
        DB::beginTransaction();

        // Transfer ownership of posts to default user
        $user->posts()->associate($defaultUser);

        // Delete all comments
        $user->comments()->delete();

        // Delete metadata
        $user->meta()->delete();

        // Save default user's changes
        $defaultUser->save();

        // Delete user
        $user->delete();

        // Commit
        DB::commit();
    }

    /**
     * Gets the default WordPress user, to assign all posts and comments to.
     *
     * @return CorcelUser
     */
    protected function getDefaultWordpressUser(): CorcelUser
    {
        // Get default user
        $defaultUser = CorcelUser::where(function ($query) {
            $query->where('login', 'gumbo')
                ->orWhere('slug', 'gumbo');
        })->first();

        // If we found a user, return it
        if ($defaultUser) {
            return $defaultUser;
        }

        // Otherwise, create one
        $defaultUser = CorcelUser::firstOrCreate([
            'user_login' => 'gumbo-millennium'
        ], [
            'user_email' => 'dc@gumbo-millennium.nl',
            'nice_name' => 'gumbo-millennium',
            'display_name' => 'Gumbo Millennium'
        ]);

        // Generate a random length password
        $password = str_random(random_int(24, 48));

        // Assign password
        $defaultUser->user_pass = (new PasswordService())->makeHash($password);

        // Save user
        $defaultUser->save();

        // Return user
        return $defaultUser;
    }
}
