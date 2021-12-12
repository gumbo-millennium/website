<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\FileExport;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Http\File;
use RuntimeException;

class FileExportFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'urlkey' => $this->faker->uuid,
            'expires_at' => $this->faker->dateTimeBetween('+1 week', '+1 month'),
        ];
    }

    public function configure()
    {
        return $this->afterMaking(function (FileExport $export) {
            $fakeFile = tempnam(sys_get_temp_dir(), 'test');

            if (! file_put_contents($fakeFile, 'test')) {
                throw new RuntimeException('Failed to create test file');
            }

            if (! $export->filename) {
                $export->attachFile(new File($fakeFile));
            }

            if (! $export->owner_id) {
                $export->owner()->associate(
                    User::query()->inRandomOrder()->first(),
                );
            }

            return $export;
        });
    }

    public function expired(): self
    {
        return $this->state([
            'expires_at' => $this->faker->dateTimeBetween('-1 year', '-1 second'),
        ]);
    }
}
