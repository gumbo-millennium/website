<?php

declare(strict_types=1);

use App\Models\Shop\Product;
use App\Models\Shop\ProductVariant;
use Faker\Generator as Faker;
use Illuminate\Http\File;
use Illuminate\Support\Facades\Storage;

$factory->define(Product::class, static function (Faker $faker) {
    return [
        'id' => $faker->uuid,
        'name' => $faker->words(3, true),
        'description' => $faker->optional()->sentences($faker->numberBetween(1, 6), true),
        'visible' => $faker->boolean(),
        'image_path' => Storage::disk('public')->putFile('shop/images', new File(resource_path('test-assets/images/protest.jpg'))),
        'etag' => $faker->md5,
        'vat_rate' => $faker->optional(0.5, 21)->randomElement([0, 6, 21]),
    ];
});

$factory->state(Product::class, 'order-limit', fn (Faker $faker) => [
    'order_limit' => $faker->numberBetween(1, 10),
]);

$factory->afterCreatingState(Product::class, 'with-variants', static function (Product $product, Faker $faker) {
    factory(ProductVariant::class, $faker->numberBetween(1, 5))->create([
        'product_id' => $product->id,
    ]);
});
