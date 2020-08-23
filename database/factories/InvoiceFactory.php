<?php

declare(strict_types=1);

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\Enrollment;
use App\Models\Invoice;
use Faker\Generator as Faker;

$providers = array_keys(Config::get('services.payments.providers', []));

$factory->define(Invoice::class, static fn (Faker $faker) => [
    'platform' => $faker->randomElement($providers),
    'platform_id' => Str::random($faker->numberBetween(5, 50)),
    'enrollment_id' => optional(Enrollment::inRandomOrder()->first(['id']))->id,
    'amount' => $faker->numberBetween(2_50, 140_00),
    'meta' => [
        'test-item' => true
    ]
]);

$factory->state(Invoice::class, 'paid', [
    'paid' => true
]);

$factory->state(Invoice::class, 'refunded', [
    'refunded' => true
]);
