<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Helpers\Str;
use App\Models\Page;
use App\Models\User;
use Faker\Generator as Faker;

$factory->define(Page::class, function (Faker $faker) {
    return [
        'title' => Str::title($faker->words($faker->randomNumber(2, 8), true)),
        'contents' => $faker->randomHtml(),
        'author_id' => optional(User::inRandomOrder()->first())->id
    ];
});
