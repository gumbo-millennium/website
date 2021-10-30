<?php

declare(strict_types=1);

use App\Models\Photos\Photo;
use Faker\Generator as Faker;

$factory->define(Photo::class, function (Faker $faker) {
    return [
        'caption' => $faker->sentence,
    ];
});
