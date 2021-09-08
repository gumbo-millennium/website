<?php

declare(strict_types=1);

use App\Models\Webcam;
use Illuminate\Database\Seeder;

class RequiredWebcamSeeder extends Seeder
{
    private const REQUIRED_WEBCAMS = [
        'plaza' => 'Plazacam',
        'coffee' => 'Koffiecam',
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Webcam::unguarded(function () {
            foreach (self::REQUIRED_WEBCAMS as $slug => $name) {
                Webcam::firstOrCreate([
                    'slug' => $slug,
                ], [
                    'name' => $name,
                    'command' => $name,
                ]);
            }
        });
    }
}
