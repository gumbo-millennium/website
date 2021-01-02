<?php

declare(strict_types=1);

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
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Add test users
        $this->makeUser('board', 'Bestuur', ['member', 'board']);
        $this->makeUser('ac', 'AC', ['member', 'ac']);
        $this->makeUser('dc', 'DC', ['member', 'dc']);
        $this->makeUser('lhw', 'LHW', ['member', 'lhw']);
        $this->makeUser('ic', 'Intro Commissie', ['member', 'ic']);
        $this->makeUser('gumbo', 'Lid', ['member']);
        $this->makeUser('guest', 'Gast', ['guest']);
        $this->makeUser('event-owner', 'Event Owner', ['member']);

        // Add more users
        factory(User::class, 25)->create();
    }

    /**
     * Creates a user.
     *
     * @param string $slug
     * @param string $name
     * @param array $roles
     * @return void
     */
    private function makeUser(string $slug, string $name, array $roles): void
    {
        $user = User::withTrashed()->updateOrCreate([
            'email' => "{$slug}@example.gumbo-millennium.nl",
        ], [
            'first_name' => $name,
            'last_name' => 'Gumbo (test)',
            'password' => Hash::make('Gumbo'),
            'email_verified_at' => now(),
        ]);
        $user->assignRole($roles);

        if (!$user->trashed()) {
            return;
        }

        $user->restore();
    }
}
