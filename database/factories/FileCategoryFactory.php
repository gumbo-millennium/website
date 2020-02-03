<?php

declare(strict_types=1);

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\FileCategory;
use Faker\Generator as Faker;

$factory->define(FileCategory::class, static fn (Faker $faker) => [
        'title' => "[test] {$faker->sentence}"
    ]);
