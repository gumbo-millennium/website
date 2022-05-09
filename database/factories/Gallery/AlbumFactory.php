<?php

declare(strict_types=1);

namespace Database\Factories\Gallery;

use App\Enums\AlbumVisibility;
use App\Models\Gallery\Album;
use App\Models\Gallery\Photo;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class AlbumFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $faker = $this->faker;

        return [
            'name' => $faker->sentence,
            'description' => $faker->optional()->sentence($faker->numberBetween(6, 15)),
        ];
    }

    public function visibility(AlbumVisibility $visibility): self
    {
        return $this->state([
            'visibility' => $visibility,
        ]);
    }

    /**
     * Mark album as private, associating a user.
     * @return AlbumFactory
     */
    public function privateFor(User $forUser): self
    {
        return $this
            ->afterMaking(fn (Album $album) => $album->user()->associate($forUser))
            ->visibility(AlbumVisibility::Private);
    }

    public function withPhotos(): self
    {
        return $this->has(
            Photo::factory()->times($this->faker->numberBetween(1, 5)),
        );
    }
}
