<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\FileCategory;
use Faker\Generator as Faker;

$factory->define(FileCategory::class, function (Faker $faker) {
    return [
        'title' => "[test] {$faker->sentence}"
    ];
});
