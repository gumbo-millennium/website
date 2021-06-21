<?php

declare(strict_types=1);

use App\Models\FileCategory;
use Faker\Generator as Faker;

$factory->define(FileCategory::class, static fn (Faker $faker) => [
    'title' => "[test category] {$faker->sentence}",
]);
