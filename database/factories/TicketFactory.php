<?php

declare(strict_types=1);

use App\Models\Ticket;
use Faker\Generator as Faker;

$factory->define(Ticket::class, fn (Faker $faker) => [
    'title' => $faker->sentence,
    'description' => $faker->optional()->paragraph,
]);

$factory->state(Ticket::class, 'timed', fn (Faker $faker) => [
    'available_from' => $faker->dateTimeBetween('-1 month', '-1 day'),
    'available_until' => $faker->dateTimeBetween('+1 day', '+1 month'),
]);

$factory->state(Ticket::class, 'paid', fn (Faker $faker) => [
    'price' => (int) round($faker->randomFloat(2, 2.50, 100) * 100),
]);

$factory->state(Ticket::class, 'private', fn () => [
    'members_only' => true,
]);
