<?php

declare(strict_types=1);

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\App;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     * @return void
     */
    public function run()
    {
        // Seed roles and permissions
        $this->call(PermissionSeeder::class);

        // Required pages
        $this->call(PageSeeder::class);

        // The rest of the seeders are not run in production
        if (App::environment(['local', 'testing'])) {
            // Add users
            $this->call(UserSeeder::class);

            // Add activities
            $this->call(ActivitySeeder::class);

            // Add a bunch of files
            $this->call(FileSeeder::class);

            // Create some news articles
            $this->call(NewsSeeder::class);
        }
    }
}
