<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\DataExport;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Storage;

class DataExportFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'created_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'updated_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
        ];
    }

    public function withData()
    {
        $this->afterMaking(function (DataExport $dataExport) {
            $path = "user-export/data/{$this->faker->uuid}.json";

            Storage::put($path, json_encode([
                'user-id' => optional($dataExport->user)->id,
                'name' => $this->faker->name,
            ]));

            $dataExport->path = $path;
            $dataExport->completed_at = $this->faker->dateTimeBetween('-1 year', 'now');
        });
    }

    public function expired()
    {
        return $this->afterCreating(function (DataExport $dataExport) {
            $dataExport->expires_at = $this->faker->dateTimeBetween('-1 year', '-1 day');
            $dataExport->save();
        });
    }
}
