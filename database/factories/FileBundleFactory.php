<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\FileBundle;
use Illuminate\Database\Eloquent\Factories\Factory;

class FileBundleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'title' => "[test bundle] {$this->faker->sentence}",
            'published_at' => $this->faker->optional(0.4)->dateTimeBetween(now()->subYear(), now()->addWeek()),
        ];
    }

    public function withFile(): self
    {
        return $this->afterMaking(function (FileBundle $bundle) {
            $bundle->addMedia(resource_path('test-assets/pdf/chicken.pdf'))
                ->preservingOriginal()
                ->toMediaCollection();
        });
    }
}
