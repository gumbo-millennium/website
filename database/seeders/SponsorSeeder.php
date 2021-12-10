<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Helpers\Str;
use App\Models\Sponsor;
use Illuminate\Database\Seeder;

class SponsorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Remove event listener
        Sponsor::unsetEventDispatcher();

        // Make sponsor
        Sponsor::factory(15)->create([
            'slug' => fn () => (string) Str::uuid(),
        ]);
    }
}
