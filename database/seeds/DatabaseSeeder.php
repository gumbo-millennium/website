<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\App;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // Seed roles and permissions
        $this->call(PermissionSeeder::class);

        // The rest of the seeders are not run in production
        if (App::environment('local')) {
            $this->call(UserSeeder::class);
            $this->call(ActivitySeeder::class);
        }
    }
}
