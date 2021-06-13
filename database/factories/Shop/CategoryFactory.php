<?php

declare(strict_types=1);

use App\Models\Shop\Category;
use Faker\Generator as Faker;

$factory->define(Category::class, static function (Faker $faker) {
    return [
        'id' => $faker->uuid,
        'name' => $faker->words(3, true),
        'description' => $faker->optional()->sentence,
        'visible' => $faker->boolean(30),
    ];
});
