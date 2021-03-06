<?php

declare(strict_types=1);

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Helpers\Str;
use App\Models\Page;
use App\Models\User;
use Faker\Generator as Faker;

$factory->define(Page::class, static fn (Faker $faker) => [
        'title' => Str::title($faker->words($faker->numberBetween(2, 8), true)),
        'contents' => '[]',
        'author_id' => optional(User::inRandomOrder()->first())->id,
    ]);
