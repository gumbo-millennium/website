<?php

declare(strict_types=1);

use App\Models\Webcam;
use Faker\Generator as Faker;

$factory->define(Webcam::class, function (Faker $faker) {
    return [
        'name' => $faker->sentence,
    ];
});

$factory->afterMaking(Webcam::class, function (Webcam $webcam, Faker $faker) {
    $webcam->command ??= Str::slug($webcam->name ?? $faker->words(2, true));
});
