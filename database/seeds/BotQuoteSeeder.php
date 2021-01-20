<?php

declare(strict_types=1);

use App\Models\BotQuote;
use App\Models\User;
use Faker\Generator;
use Illuminate\Database\Seeder;

class BotQuoteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(Generator $faker): void
    {
        factory(BotQuote::class, 70)->state('sent')->create([
            'user_id' => null,
        ]);
        factory(BotQuote::class, 15)->create([
            'user_id' => null,
        ]);

        foreach (User::query()->where('email', 'like', '%@example.gumbo-millennium.nl')->get() as $user) {
            factory(BotQuote::class)
                ->times($faker->numberBetween(1, 30))
                ->state('sent')
                ->create(['user_id' => null]);

            factory(BotQuote::class)
                ->times($faker->numberBetween(1, 5))
                ->create(['user_id' => null]);
        }
    }
}
