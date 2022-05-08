<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Gallery\Album;
use App\Models\Gallery\Photo;
use App\Models\Gallery\PhotoReaction;
use App\Models\Gallery\PhotoReport;
use Faker\Generator;
use Illuminate\Database\Seeder;
use Illuminate\Foundation\Testing\WithFaker;

class GallerySeeder extends Seeder
{
    use WithFaker;

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(Generator $faker)
    {
        $albums = Album::factory($faker->numberBetween(2, 10))->create();

        foreach ($albums as $album) {
            // Empty albums (20% of albums)
            if ($faker->boolean(20)) {
                continue;
            }

            // Create a number of photos
            $photos = Photo::factory($faker->numberBetween(2, 10))->for($album)->create();

            foreach ($photos as $photo) {
                // Photo with likes (45% of photos)
                if ($faker->boolean(45)) {
                    PhotoReaction::factory($faker->numberBetween(1, 10))->for($photo)->create();
                }

                // Photo with reports (5% of photos)
                if ($faker->boolean(5)) {
                    PhotoReport::factory($faker->numberBetween(1, 4))->for($photo)->create();
                }
            }
        }
    }
}
