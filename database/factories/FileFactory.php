<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\File;
use Faker\Generator as Faker;

// Create instance
$factory->define(File::class, function (Faker $faker) {
    return [
        'title' => "[test] {$faker->sentence}",
    ];
});

// Attach chicken
$factory->afterMaking(File::class, function (File $file) {
    $file->file = new \SplFileInfo(resource_path('assets/pdf/chicken.pdf'));
});
