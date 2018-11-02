<?php

use App\User;
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
        $user = User::updateOrCreate([
            'email' => 'gumbo@docker.local'
        ], [
            'name' => 'Gumbo Millennium',
            'password' => Hash::make('Gumbo')
        ]);
        $user->assignRole(['guest', 'member', 'dc']);
    }
}
