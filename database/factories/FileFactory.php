<?php

declare(strict_types=1);

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\File;
use Faker\Generator as Faker;

// Create instance
$factory->define(File::class, static fn (Faker $faker) => [
        'title' => "[test] {$faker->sentence}",
    ]);

// Attach chicken
$factory->afterMaking(File::class, static function (File $file) {
    $file->file = new \SplFileInfo(resource_path('assets/pdf/chicken.pdf'));
});
