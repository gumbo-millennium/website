<?php

declare(strict_types=1);

namespace Database\Factories\Shop;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'paid_at' => $this->faker->optional()->dateTimeBetween('-1 year', '5 seconds ago'),
            'user_id' => User::inRandomOrder()->first()->id,
            'price' => $this->faker->numberBetween(150, 15000),
        ];
    }
}
