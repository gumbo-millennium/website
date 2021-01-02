<?php

declare(strict_types=1);

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
        factory(Sponsor::class, 15)->create([
            'slug' => static fn () => (string) Str::uuid(),
        ]);
    }
}
