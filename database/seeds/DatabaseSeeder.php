<?php

use Illuminate\Database\Seeder;

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

        // Seed a default category
        $this->call(FileCategorySeeder::class);

        // The rest of the seeders are not run in production
        if (config('app.env') === 'local') {
            $this->call(UserSeeder::class);
        }
    }
}
