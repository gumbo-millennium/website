<?php

declare(strict_types=1);

namespace Database\Factories\Shop;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Http\File;
use Illuminate\Support\Facades\Storage;

class ProductVariantFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $image = $this->faker->optional(0.25)->passthrough(true);

        if ($image) {
            $image = Storage::disk('public')->putFile(
                'shop/images',
                new File(resource_path('test-assets/images/roadtrip.jpg')),
            );
        }

        return [
            'id' => $this->faker->uuid,
            'name' => $this->faker->words(3, true),
            'description' => $this->faker->optional()->sentences(
                $this->faker->numberBetween(1, 6),
                true,
            ),
            'image_path' => $image,
            'sku' => $this->faker->ean13,
            'price' => $this->faker->numberBetween(250, 3000),
            'order' => $this->faker->numerify(1, 10),
        ];
    }

    public function orderLimit()
    {
        return $this->state([
            'order_limit' => $this->faker->numberBetween(1, 10),
        ]);
    }
}
