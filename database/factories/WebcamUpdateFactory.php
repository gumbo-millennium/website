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
    $image = new File($faker->image(sys_get_temp_dir(), 64, 64));
    $webcamUpdate->path = Storage::putFile(WebcamUpdate::STORAGE_LOCATION, $image);
});
