<?php

declare(strict_types=1);

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class TicketFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'title' => $this->faker->sentence,
            'description' => $this->faker->optional()->paragraph,
        ];
    }

    public function timed()
    {
        return $this->state([
            'available_from' => $this->faker->dateTimeBetween('-1 month', '-1 day'),
            'available_until' => $this->faker->dateTimeBetween('+1 day', '+1 month'),
        ]);
    }

    public function paid()
    {
        return $this->state([
            'price' => (int) round($this->faker->randomFloat(2, 2.50, 100) * 100),
        ]);
    }

    public function private()
    {
        return $this->state([
            'is_public' => false,
        ]);
    }
}
