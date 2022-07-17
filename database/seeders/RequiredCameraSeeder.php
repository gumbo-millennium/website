<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Webcam\Camera;
use Illuminate\Database\Seeder;

class RequiredCameraSeeder extends Seeder
{
    private const REQUIRED_CAMERAS = [
        'plaza' => 'Plazacam',
        'coffee' => 'Koffiecam',
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Camera::unguarded(function () {
            foreach (self::REQUIRED_CAMERAS as $slug => $name) {
                Camera::firstOrCreate([
                    'slug' => $slug,
                ], [
                    'name' => $name,
                    'command' => $name,
                ]);
            }
        });
    }
}
