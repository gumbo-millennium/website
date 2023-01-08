<?php

declare(strict_types=1);

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ActivityMessageFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'subject' => $this->faker->sentence,
            'body' => <<<DOC
            ## {$this->faker->sentence()}

            {$this->faker->sentences(6, true)}
            DOC,
        ];
    }

    public function scheduled(): self
    {
        return $this->state([
            'scheduled_at' => $this->faker->dateTimeBetween('+1 hour', '+1 month'),
        ]);
    }
}
