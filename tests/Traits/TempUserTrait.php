<?php

declare(strict_types=1);

namespace Tests\Traits;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Creates a temporary user and deletes it after the tests are done.
 */
trait TempUserTrait
{
    /**
     * Sets up a new user on a dump e-mail with the given roles.
     *
     * @param null|bool $emailValidated Set to false to mark user as non-validated
     */
    public static function getUser(array $roles, ?bool $emailValidated = null): User
    {
        $user = User::firstOrCreate([
            'email' => 'test@example.com',
        ], [
            'first_name' => 'Test',
            'last_name' => 'User',
            'password' => Hash::make(Str::random(20)),
        ]);

        // Mark e-mail as validated or not, defaults to yes
        $user->email_verified_at = $emailValidated !== false ? now() : null;
        $user->save();

        // Wipe roles on user
        $user->syncRoles($roles);

        // Return user
        return $user;
    }

    /**
     * Deletes temp user after class completes.
     *
     * @afterClass
     */
    public function tearDownTempUser()
    {
        User::where([
            'email' => 'test@example.com',
        ])->delete();
    }
}
