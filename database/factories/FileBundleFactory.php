<?php

declare(strict_types=1);

use App\Models\FileBundle;
use Faker\Generator as Faker;
use Illuminate\Http\File;

// Create instance
$factory->define(FileBundle::class, fn (Faker $faker) => [
    'title' => "[test bundle] {$faker->sentence}",
    'published_at' => $faker->optional(0.4)->dateTimeBetween(now()->subYear(), now()->addWeek()),
]);

// Attach chicken
$factory->afterMakingState(FileBundle::class, 'with-file', function (FileBundle $bundle) {
    $bundle->addMedia(new File(resource_path('test-assets/pdf/chicken.pdf')))
        ->preservingOriginal()
        ->toMediaCollection();
});
