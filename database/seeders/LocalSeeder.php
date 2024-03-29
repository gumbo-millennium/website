<?php

declare(strict_types=1);

namespace Database\Seeders;

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
        $this->call(LocalRoleSeeder::class);
        $this->call(UserSeeder::class);

        // Seed shop
        $this->call(ShopSeeder::class);

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

        // Add some gallery albums
        $this->call(GallerySeeder::class);
    }
}
