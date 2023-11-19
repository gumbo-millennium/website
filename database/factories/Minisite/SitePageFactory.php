<?php

declare(strict_types=1);

namespace Database\Factories\Minisite;

use App\Helpers\Str;
use App\Models\Minisite\Site;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @method static hasSite(Site $site)
 * @method static forSite(SiteFactory $factory)
 */
class SitePageFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'title' => Str::title($this->faker->words($this->faker->numberBetween(2, 8), true)),
            'contents' => '[]',
            'visible' => true,
        ];
    }

    public function visible(bool $visible = true): self
    {
        return $this->state([
            'visible' => $visible,
        ]);
    }
}
