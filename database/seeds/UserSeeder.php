<?php

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Adds or updates the default user
 *
 * @author Roelof Roos <github@roelof.io>
 * @license MPL-2.0
 */
class UserSeeder extends Seeder
{
    /**
     * Creates a user
     *
     * @param string $email
     * @param array $roles
     * @return void
     */
    private function makeUser(string $email, string $name, array $roles): void
    {
        $user = User::withTrashed()->updateOrCreate([
            'email' => $email
        ], [
            'first_name' => $name,
            'last_name' => 'Gumbo (test)',
            'password' => Hash::make('Gumbo')
        ]);
        $user->assignRole($roles);

        if ($user->trashed()) {
            $user->restore();
        }
    }

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Add test users
        $this->makeUser('board@example.com', 'Bestuur', ['member', 'board']);
        $this->makeUser('ac@example.com', 'AC', ['member', 'ac']);
        $this->makeUser('dc@example.com', 'DC', ['member', 'dc']);
        $this->makeUser('lhw@example.com', 'LHW', ['member', 'lhw']);
        $this->makeUser('gumbo@example.com', 'Lid', ['member']);
        $this->makeUser('guest@example.com', 'Gast', ['guest']);
        $this->makeUser('event-owner@example.com', 'Event Owner', ['member']);

        // Add more users
        factory(User::class, 25)->create();
    }
}
