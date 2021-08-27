<?php

declare(strict_types=1);

use App\Models\RedirectInstruction;
use Faker\Generator as Faker;

$factory->define(RedirectInstruction::class, function (Faker $faker) {
    return [
        'slug' => parse_url($faker->url, PHP_URL_PATH),
        'path' => parse_url($faker->url, PHP_URL_PATH),
    ];
});
