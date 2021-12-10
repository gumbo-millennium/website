<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class BotQuoteFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $createdAt = $this->faker->dateTimeBetween('-5 months', '+4 weeks');

        return [
            'created_at' => $createdAt,
            'updated_at' => $createdAt,
            'submitted_at' => null,
            'user_id' => $this->faker->optional(0.4)->passthrough(User::inRandomOrder()->first(['id'])->id),
            'display_name' => $this->faker->name,
            'quote' => $this->faker->sentence,
        ];
    }

    public function sent()
    {
        return $this->state([
            'submitted_at' => $this->faker->dateTimeBetween('-5 months', 'now'),
        ]);
    }
}
