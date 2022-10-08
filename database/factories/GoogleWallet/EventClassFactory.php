<?php

declare(strict_types=1);

namespace Database\Factories\GoogleWallet;

use Illuminate\Database\Eloquent\Factories\Factory;

class EventClassFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->name,
            'location_name' => $this->faker->streetAddress,
            'start_time' => $this->faker->dateTimeBetween('now', '+1 year'),
            'end_time' => $this->faker->dateTimeBetween('now', '+1 year'),
        ];
    }
}
