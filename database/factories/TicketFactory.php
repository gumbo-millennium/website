<?php

declare(strict_types=1);

use App\Models\Ticket;
use Faker\Generator as Faker;

$factory->define(Ticket::class, function (Faker $faker) {
    return [
        'title' => $faker->sentence,
        'description' => $faker->optional()->paragraph,
    ];
});

$factory->state(Ticket::class, 'timed', function (Faker $faker) {
    return [
        'available_from' => $faker->dateTimeBetween('-1 month', '-1 day'),
        'available_until' => $faker->dateTimeBetween('+1 day', '+1 month'),
    ];
});

$factory->state(Ticket::class, 'paid', function (Faker $faker) {
    return [
        'price' => round($faker->randomFloat(2, 2.5, 100) * 100),
    ];
});
