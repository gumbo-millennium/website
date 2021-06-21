<?php

declare(strict_types=1);

use App\Models\FileBundle;
use Faker\Generator as Faker;
use Illuminate\Http\File;

// Create instance
$factory->define(FileBundle::class, static fn (Faker $faker) => [
    'title' => "[test bundle] {$faker->sentence}",
    'published_at' => $faker->optional(0.4)->dateTimeBetween(now()->subYear(), now()->addWeek()),
]);

// Attach chicken
$factory->afterMaking(FileBundle::class, static function (FileBundle $bundle) {
    $bundle->addMedia(new File(resource_path('test-assets/pdf/chicken.pdf')));
});
