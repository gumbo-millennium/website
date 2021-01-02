<?php

declare(strict_types=1);

use Illuminate\Database\Seeder;

class LocalSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
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
