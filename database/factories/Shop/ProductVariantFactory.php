<?php

declare(strict_types=1);

use App\Models\Shop\ProductVariant;
use Faker\Generator as Faker;

$factory->define(ProductVariant::class, static function (Faker $faker) {
    return [
        'id' => $faker->uuid,
        'name' => $faker->words(3, true),
        'description' => $faker->optional()->sentences($faker->numberBetween(1, 6), true),
        'image_url' => $faker->optional(0.25)
            ->passthrough(sprintf('https://loremflickr.com/320/240?lock=%s', $faker->randomNumber(6))),
        'sku' =>  $faker->ean13,
        'price' =>  $faker->numberBetween(250, 3000),
        'order' => $faker->numerify(1, 10),
    ];
});

$factory->state(ProductVariant::class, 'order-limit', fn (Faker $faker) => [
    'order_limit' => $faker->numberBetween(1, 10),
]);
