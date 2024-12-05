<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Helpers\Str;
use App\Models\ModelExport;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ModelExport>
 */
class ModelExportFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'generated_at' => now(),

            'job' => 'App\\Jobs\\DummyJob',

            'disk' => 'local',
            'name' => sprintf('%s.pdf', Str::slug(Str::words(3, true))),
            'path' => sprintf('test/export/%s.pdf', Str::uuid()),
        ];
    }

    public function forModel(Model|Factory $model): self
    {
        return $this->for($model, 'model');
    }

    public function forUser(User|UserFactory $user): self
    {
        return $this->for($user, 'user');
    }

    public function withFile(): self
    {
        return $this->afterMaking(function (ModelExport $export): void {
            $export->saveFile(resource_path('test-assets/pdf/chicken.pdf'));
            $export->name = "{$this->faker->words(3, true)}.pdf";
        });
    }
}
