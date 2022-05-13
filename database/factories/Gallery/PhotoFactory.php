<?php

declare(strict_types=1);

namespace Database\Factories\Gallery;

use App\Enums\PhotoVisibility;
use App\Helpers\Str;
use App\Models\Gallery\Album;
use App\Models\Gallery\Photo;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Http\File;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class PhotoFactory extends Factory
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
            'name' => $faker->randomElement([
                $faker->word,
                $faker->sentence,
            ]),
            'description' => Str::limit($faker->optional()->sentence($faker->numberBetween(6, 30)), 240) ?: null,
            'visibility' => PhotoVisibility::Visible,
        ];
    }

    public function visibility(PhotoVisibility $visibility): self
    {
        return $this->state([
            'visibility' => $visibility,
        ]);
    }

    public function configure()
    {
        return $this->afterMaking(function (Photo $photo) {
            if ($photo->album === null) {
                $photo->album()->associate(Album::factory()->create());
            }

            $photoDisk = Config::get('gumbo.images.disk');
            $photoPath = Config::get('gumbo.images.path');

            if ($photo->path !== null) {
                return;
            }

            $storedImage = Storage::disk($photoDisk)->putFile("${photoPath}/seeded/gallery-photos", new File($this->faker->image()));
            if ($storedImage === false) {
                Log::warning("Failed to write image to disk");

                return;
            }

            $photo->path ??= $storedImage;
            $photo->taken_at = $this->faker->dateTimeBetween('-1 year', 'now');
        });
    }

    public function removed(): self
    {
        return $this->state(fn () => [
            'removal_reason' => $this->faker->sentence,
            'deleted_at' => $this->faker->dateTimeBetween('-1 years', '-1 days'),
        ]);
    }
}
