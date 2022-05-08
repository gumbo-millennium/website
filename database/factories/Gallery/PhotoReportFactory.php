<?php

declare(strict_types=1);

namespace Database\Factories\Gallery;

use App\Enums\PhotoReportResolution;
use App\Models\Gallery\Photo;
use App\Models\Gallery\PhotoReport;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PhotoReportFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'reason' => $this->faker->sentence,
            'resolution' => PhotoReportResolution::Pending->value,
        ];
    }

    public function resolved(): self
    {
        return  $this->state(function () {
            $faker = $this->faker;

            /** @var PhotoReportResolution */
            $resolution = $faker->optional(0.5, PhotoReportResolution::Pending)->randomElement(PhotoReportResolution::cases());

            return [
                'resolution' => $resolution->value,
                'resolved_at' => $faker->dateTimeBetween('-1 years', '-1 days'),
            ];
        });
    }

    public function configure()
    {
        return $this->afterMaking(function (PhotoReport $report) {
            if ($report->user === null) {
                $report->user()->associate(User::factory()->create());
            }
            if ($report->photo === null) {
                $report->photo()->associate(Photo::factory()->create());
            }
        });
    }
}
