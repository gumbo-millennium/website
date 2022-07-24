<?php

declare(strict_types=1);

namespace Database\Factories\Webcam;

use App\Helpers\Str;
use App\Models\Webcam\Camera;
use Illuminate\Database\Eloquent\Factories\Factory;

class CameraFactory extends Factory
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
        return $this->afterMaking(function (Camera $camera) {
            $camera->command ??= Str::slug($camera->name ?? $this->faker->words(2, true));
        });
    }
}
