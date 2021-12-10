<?php

declare(strict_types=1);

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class SponsorClickFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return  [
            'count' => $this->faker->numberBetween(1, 500),
            'date' => $this->faker->unique()->date(),
        ];
    }
}
