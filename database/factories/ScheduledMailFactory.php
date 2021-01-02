<?php

declare(strict_types=1);

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\ScheduledMail;
use Faker\Generator as Faker;

$generatorNodes = [
    'Activity',
    'Enrollment',
    'File',
];

$factory->define(ScheduledMail::class, static fn (Faker $faker) => [
    'group' => sprintf("%s:%s", $faker->randomElement($generatorNodes), $faker->numberBetween()),
    'name' => $faker->word,
    'scheduled_for' => $faker->dateTimeBetween('-60 days', '+60 days'),
    'sent_at' => $faker->optional(0.7)->dateTimeBetween('-60 days', '+60 days'),
]);
