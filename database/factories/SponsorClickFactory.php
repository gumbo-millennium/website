<?php

declare(strict_types=1);

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\SponsorClick;
use Faker\Generator as Faker;

$factory->define(SponsorClick::class, static fn (Faker $faker) => [
    'count' => $faker->numberBetween(1, 500),
    'date' => $faker->unique()->date()
]);
