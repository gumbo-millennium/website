<?php

declare(strict_types=1);

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class JoinSubmissionFactory extends Factory
{
    private const NAME_INSERTS = [
        'van',
        'de',
        'van het',
        'van \'t',
        'ter',
        'van de',
    ];

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            // Names
            'first_name' => $this->faker->firstName,
            'insert' => $this->faker->optional(0.4)->randomElement(self::NAME_INSERTS),
            'last_name' => $this->faker->lastName,

            // Contact info
            'phone' => $this->faker->phoneNumber,
            'email' => $this->faker->safeEmail,

            // User details
            'date_of_birth' => $this->faker->dateTimeBetween('30 years ago', '16 years ago')->format('d-m-Y'),
            'gender' => $this->faker->randomElement([
                ...array_fill(0, 10, 'Man'),
                ...array_fill(0, 10, 'Vrouw'),
                'Non-Binary',
                'Zeg ik liever niet',
            ]),

            // Contact data
            'street' => $this->faker->streetName,
            'number' => $this->faker->buildingNumber,
            'city' => $this->faker->city,
            'postal_code' => $this->faker->postcode,
            'country' => $this->faker->optional(0.1, 'NL')->countryCode,

            // Result
            'granted' => $this->faker->optional(0.15)->boolean(),
        ];
    }
}
