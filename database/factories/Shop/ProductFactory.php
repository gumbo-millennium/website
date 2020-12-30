<?php

declare(strict_types=1);

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\Shop\Product;
use Faker\Generator as Faker;

$factory->define(Product::class, static function (Faker $faker) {
    return [
        'id' => $faker->uuid,
        'name' => $faker->words(3, true),
        'description' => $faker->optional()->sentence,
        'visible' => $faker->boolean(),
        'image_url' => sprintf('https://loremflickr.com/320/240?lock=%s', $faker->randomNumber(6)),
        'etag' => $faker->md5,
        'vat_rate' => $faker->optional(0.5, 21)->randomElement([0, 6, 21]),
    ];
});