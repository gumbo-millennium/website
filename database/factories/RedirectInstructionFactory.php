<?php

declare(strict_types=1);

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class RedirectInstructionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'slug' => parse_url($this->faker->url, PHP_URL_PATH),
            'path' => parse_url($this->faker->url, PHP_URL_PATH),
        ];
    }
}
