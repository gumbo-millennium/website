<?php

declare(strict_types=1);

namespace Database\Factories\Shop;

use Illuminate\Database\Eloquent\Factories\Factory;

class CategoryFactory extends Factory
{
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
}
