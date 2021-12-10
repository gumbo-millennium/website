<?php

declare(strict_types=1);

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ScheduledMailFactory extends Factory
{
    private const GENERATOR_NODES = [
        'Activity',
        'Enrollment',
        'File',
    ];

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'group' => sprintf('%s:%s', $this->faker->randomElement(self::GENERATOR_NODES), $this->faker->numberBetween()),
            'name' => $this->faker->word,
            'scheduled_for' => $this->faker->dateTimeBetween('-60 days', '+60 days'),
            'sent_at' => $this->faker->optional(0.7)->dateTimeBetween('-60 days', '+60 days'),
        ];
    }
}
