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
            $imageFile = $this->faker->image(sys_get_temp_dir(), 64, 64);

            $device->image = Storage::disk(Config::get('gumbo.images.disk'))
                ->putFile(Device::STORAGE_FOLDER, new File($imageFile));
        });
    }
}
