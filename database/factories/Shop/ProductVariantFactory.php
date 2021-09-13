<?php

declare(strict_types=1);

use App\Models\Shop\ProductVariant;
use Faker\Generator as Faker;
use Illuminate\Http\File;

$factory->define(ProductVariant::class, static function (Faker $faker) {
    $image = $faker->optional(0.25)->passthrough(true);

    if ($image) {
        $image = Storage::disk('public')->putFile('shop/images', new File(resource_path('test-assets/images/roadtrip.jpg')));
    }

    return [
        'id' => $faker->uuid,
        'name' => $faker->words(3, true),
        'description' => $faker->optional()->sentences($faker->numberBetween(1, 6), true),
        'image_path' => $image,
        'sku' => $faker->ean13,
        'price' => $faker->numberBetween(250, 3000),
        'order' => $faker->numerify(1, 10),
    ];
});

$factory->state(ProductVariant::class, 'order-limit', fn (Faker $faker) => [
    'order_limit' => $faker->numberBetween(1, 10),
]);
