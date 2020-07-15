<?php

declare(strict_types=1);

use App\Models\Sponsor;
use Illuminate\Database\Seeder;

class SponsorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * @return void
     */
    public function run()
    {
        // Create 8 random sponsors
        \factory(Sponsor::class, 8);

        // Create 15 more without banner
        \factory(Sponsor::class, 15)->create([
            'backdrop' => null
        ]);
    }
}
