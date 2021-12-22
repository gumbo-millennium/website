<?php

declare(strict_types=1);

namespace Database\Factories\Shop;

use App\Models\Shop\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Http\File;
use Illuminate\Support\Facades\Storage;

class ProductFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var null|string
     */
    protected $model = Product::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'id' => $this->faker->uuid,
            'name' => $this->faker->words(3, true),
            'description' => $this->faker->optional()->sentences($this->faker->numberBetween(1, 6), true),
            'visible' => $this->faker->boolean(),
            'image_path' => Storage::disk('public')->putFile('shop/images', new File(resource_path('test-assets/images/protest.jpg'))),
            'etag' => $this->faker->md5,
            'vat_rate' => $this->faker->optional(0.5, 21)->randomElement([0, 6, 21]),
        ];
    }

    public function orderLimit(): self
    {
        return $this->state([
            'order_limit' => $this->faker->numberBetween(1, 10),
        ]);
    }

    public function withVariants(): self
    {
        return $this->hasVariants($this->faker->numberBetween(1, 5));
    }

    public function visible(bool $visible = true): self
    {
        return $this->state([
            'visible' => $visible,
        ]);
    }
}
