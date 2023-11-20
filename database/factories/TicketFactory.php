<?php

declare(strict_types=1);

namespace Database\Factories;

use DateTimeInterface;
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

    public function timed(?DateTimeInterface $from = null, ?DateTimeInterface $until = null): self
    {
        return $this->state([
            'available_from' => $from ?? $this->faker->dateTimeBetween('-1 month', '-1 day'),
            'available_until' => $until ?? $this->faker->dateTimeBetween('+1 day', '+1 month'),
        ]);
    }

    public function paid(?int $price = null): self
    {
        return $this->state([
            'price' => $price ?? $this->faker->numberBetween(2_50, 100_00),
        ]);
    }

    public function public(): self
    {
        return $this->state([
            'is_public' => true,
        ]);
    }

    public function private(): self
    {
        return $this->state([
            'is_public' => false,
        ]);
    }
}
