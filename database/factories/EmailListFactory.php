<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Helpers\Str;
use App\Services\Mail\GoogleMailList;
use Illuminate\Database\Eloquent\Factories\Factory;

class EmailListFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        // Prep a set of members
        $emails = [];
        for ($i = 0; $i < $this->faker->numberBetween(1, 20); $i++) {
            $emails[] = [
                'email' => $this->faker->safeEmail,
                'role' => $this->faker->boolean(95) ? GoogleMailList::ROLE_NAME_NORMAL : GoogleMailList::ROLE_NAME_ADMIN,
            ];
        }

        // Prep some aliases
        $aliases = [];
        for ($i = 0; $i < $this->faker->numberBetween(0, 6); $i++) {
            $aliases[] = $this->faker->safeEmail;
        }

        // Done
        return [
            'name' => $this->faker->words(3, true),
            'email' => $this->faker->safeEmail,
            'service_id' => Str::random(16),
            'aliases' => $aliases,
            'members' => $emails,
        ];
    }
}
