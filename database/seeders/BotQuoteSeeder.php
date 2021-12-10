<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\BotQuote;
use App\Models\User;
use Faker\Generator;
use Illuminate\Database\Seeder;

class BotQuoteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(Generator $faker): void
    {
        BotQuote::factory()->times(70)->sent()->create([
            'user_id' => null,
        ]);
        BotQuote::factory()->times(15)->create([
            'user_id' => null,
        ]);

        foreach (User::query()->where('email', 'like', '%@example.gumbo-millennium.nl')->get() as $user) {
            BotQuote::factory()
                ->times($faker->numberBetween(1, 30))
                ->sent()
                ->for($user)
                ->create();

            BotQuote::factory()
                ->times($faker->numberBetween(1, 5))
                ->for($user)
                ->create();
        }
    }
}
