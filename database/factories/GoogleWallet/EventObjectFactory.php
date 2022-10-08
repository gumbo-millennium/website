<?php

declare(strict_types=1);

namespace Database\Factories\GoogleWallet;

use Illuminate\Database\Eloquent\Factories\Factory;

class EventObjectFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'value' => money_value($this->faker->optional()->numberBetween(1_00, 200_00)),
            'ticket_number' => $this->faker->uuid,
            'ticket_type' => $this->faker->word,
            'barcode' => $this->faker->asciify('************'),
        ];
    }
}
