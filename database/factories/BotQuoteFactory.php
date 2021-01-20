<?php

declare(strict_types=1);

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\BotQuote;
use App\Models\User;
use Faker\Generator as Faker;

$factory->define(BotQuote::class, static function (Faker $faker) {
    $createdAt = $faker->dateTimeBetween('-5 months', '+4 weeks');

    return [
        'created_at' => $createdAt,
        'updated_at' => $createdAt,
        'submitted_at' => null,
        'user_id' => $faker->optional(0.4)->passthrough(User::inRandomOrder()->first(['id'])->id),
        'display_name' => $faker->name,
        'quote' => $faker->sentence,
    ];
});

$factory->state(BotQuote::class, 'sent', static fn (Faker $faker) => [
    'submitted_at' => $faker->dateTimeBetween('-5 months', 'now'),
]);
