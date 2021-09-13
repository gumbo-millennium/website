<?php

declare(strict_types=1);

use App\Models\WebcamUpdate;
use Faker\Generator as Faker;
use Illuminate\Http\File;
use Illuminate\Support\Facades\Storage;

$factory->define(WebcamUpdate::class, function (Faker $faker) {
    return [
        'ip' => $faker->randomElement([$faker->ipv4, $faker->ipv6]),
        'user_agent' => $faker->userAgent,

        'created_at' => $date = $faker->dateTimeBetween('-3 hours', '-30 min'),
        'updated_at' => $date,
    ];
});

$factory->afterMakingState(WebcamUpdate::class, 'with-image', function (WebcamUpdate $webcamUpdate, Faker $faker) {
    $image = new File(resource_path($faker->randomElement([
        'test-assets/images/squares/square-red.png',
        'test-assets/images/squares/square-green.png',
        'test-assets/images/squares/square-blue.png',
        'test-assets/images/squares/square-orange.png',
        'test-assets/images/squares/square-yellow.png',
    ])));

    $webcamUpdate->path = Storage::putFile(WebcamUpdate::STORAGE_LOCATION, $image);
});
