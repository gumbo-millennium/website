<?php

declare(strict_types=1);

namespace Database\Factories\Shop;

use App\Models\Shop\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

class CategoryFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var null|string
     */
    protected $model = Category::class;

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
            'description' => $this->faker->optional()->sentence,
            'visible' => $this->faker->boolean(30),
        ];
    }

    public function visible(bool $visible = true): self
    {
        return $this->state([
            'visible' => $visible,
        ]);
    }
}
