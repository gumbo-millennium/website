<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Helpers\Str;
use App\Models\Webcam;
use Illuminate\Database\Eloquent\Factories\Factory;

class WebcamFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->sentence,
        ];
    }

    public function configure()
    {
        return $this->afterMaking(function (Webcam $webcam) {
            $webcam->command ??= Str::slug($webcam->name ?? $this->faker->words(2, true));
        });
    }
}
