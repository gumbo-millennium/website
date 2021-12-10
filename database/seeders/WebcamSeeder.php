<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Webcam;
use App\Models\WebcamUpdate;
use Faker\Generator as Faker;
use Illuminate\Database\Seeder;

class WebcamSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(Faker $faker): void
    {
        // Make cams
        $cams = Webcam::factory(3)->create();

        // Make updates for each cam
        foreach ($cams as $cam) {
            WebcamUpdate::factory($faker->numberBetween(5, 100))->create([
                'webcam_id' => $cam->id,
            ]);

            $images = $faker->numberBetween(5, 20);
            for ($i = 0; $i < $images; $i++) {
                WebcamUpdate::factory()->withImage()->create([
                    'webcam_id' => $cam->id,
                    'created_at' => $date = $faker->dateTimeBetween('-1 day', 'now'),
                    'updated_at' => $date,
                ]);
            }
        }
    }
}
