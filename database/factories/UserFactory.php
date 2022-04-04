<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Helpers\Str;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

class UserFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'telegram_id' => $this->faker->optional(0.8)->numerify(str_repeat('#', 31)),
            'first_name' => $this->faker->firstName,
            'last_name' => $this->faker->lastName,
            'email' => $this->faker->unique()->safeEmail,
            'gender' => $this->faker->randomElement(['Man', 'Vrouw', $this->faker->word, null]),
            'password' => Hash::make('password'),
            'remember_token' => Str::random(10),
        ];
    }

    public function withRole(string $role): self
    {
        return $this->afterCreating(fn (User $user) => $user->assignRole($role));
    }
}
