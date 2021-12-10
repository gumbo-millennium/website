<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\WebcamUpdate;
use Database\Factories\Traits\HasFileFinder;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Storage;

class WebcamUpdateFactory extends Factory
{
    use HasFileFinder;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'ip' => $this->faker->randomElement([$this->faker->ipv4, $this->faker->ipv6]),
            'user_agent' => $this->faker->userAgent,

            'created_at' => $date = $this->faker->dateTimeBetween('-3 hours', '-30 min'),
            'updated_at' => $date,
        ];
    }

    public function withImage()
    {
        return $this->afterMaking(function (WebcamUpdate $webcamUpdate) {
            $webcamUpdate->path = Storage::putFile(
                WebcamUpdate::STORAGE_LOCATION,
                $this->findFiles('test-assets/images/squares', 'png')->random(),
            );
        });
    }
}
