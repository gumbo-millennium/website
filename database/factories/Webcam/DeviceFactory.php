<?php

declare(strict_types=1);

namespace Database\Factories\Webcam;

use App\Models\Webcam\Device;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Http\File;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;

class DeviceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'device' => $this->faker->unique()->uuid(),
            'name' => $this->faker->slug(4),
        ];
    }

    /**
     * Create an image for the device after creating it.
     */
    public function withImage(): self
    {
        return $this->afterMaking(function (Device $device) {
            $image = $this->faker->randomElement([
                'test-assets/images/squares/square-blue.png',
                'test-assets/images/squares/square-green.png',
                'test-assets/images/squares/square-orange.png',
                'test-assets/images/squares/square-red.png',
                'test-assets/images/squares/square-yellow.png',
            ]);

            $device->path = Storage::disk(Config::get('gumbo.images.disk', 'local'))
                ->putFile(Device::STORAGE_FOLDER, new File(resource_path($image)));
        });
    }
}
