<?php

declare(strict_types=1);

namespace Database\Factories\Minisite;

use Illuminate\Database\Eloquent\Factories\Factory;

class SiteFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'domain' => $this->faker->domainName(),
            'name' => $this->faker->company(),
            'enabled' => false,
        ];
    }

    public function enabled(bool $enabled = true): self
    {
        return $this->state([
            'enabled' => $enabled,
        ]);
    }
}
