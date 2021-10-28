<?php

declare(strict_types=1);

use App\Photos\Album;
use Faker\Generator as Faker;

$factory->define(Album::class, function (Faker $faker) {
    return [
        'name' => $faker->sentence,
    ];
});
