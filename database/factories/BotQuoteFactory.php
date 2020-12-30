<?php

declare(strict_types=1);

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\BotQuote;
use App\Models\User;
use Faker\Generator as Faker;

$factory->define(BotQuote::class, static function (Faker $faker) {
    $createdAt = $faker->dateTimeBetween('-5 months', '+4 weeks');
    $submittedAt = $faker->optional(0.8)->dateTimeBetween('-5 months', 'now');

    return [
        'created_at' => $createdAt,
        'updated_at' => $submittedAt ?? $createdAt,
        'submitted_at' => $submittedAt,
        'user_id' => $faker->optional(0.4)->passthrough(User::inRandomOrder()->first(['id'])->id),
        'display_name' => $faker->name,
        'quote' => $faker->sentence,
    ];
});
