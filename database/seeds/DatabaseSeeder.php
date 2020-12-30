<?php

declare(strict_types=1);

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

        // Required pages
        $this->call(PageSeeder::class);

        // The rest of the seeders are not run in production
        if (!App::environment(['local', 'testing'])) {
            return;
        }

        // Add users
        $this->call(UserSeeder::class);

        // Add bot quotes
        $this->call(BotQuoteSeeder::class);

        // Add activities
        $this->call(ActivitySeeder::class);

        // Add a bunch of file bundles
        $this->call(FileBundleSeeder::class);

        // Create some news articles
        $this->call(NewsSeeder::class);

        // Add some sponsors
        $this->call(SponsorSeeder::class);
    }
}
