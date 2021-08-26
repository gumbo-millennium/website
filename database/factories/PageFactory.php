<?php

declare(strict_types=1);

use App\Helpers\Str;
use App\Models\Page;
use App\Models\User;
use Faker\Generator as Faker;

$factory->define(Page::class, static fn (Faker $faker) => [
    'title' => Str::title($faker->words($faker->numberBetween(2, 8), true)),
    'contents' => '[]',
    'author_id' => optional(User::inRandomOrder()->first())->id,
]);

$factory->state(Page::class, 'with_summary', static fn (Faker $faker) => [
    'summary' => $faker->sentence,
]);

$factory->state(Page::class, 'with_contents', static fn (Faker $faker) => [
    'contents' => json_encode([
        'blocks' => [
            [
                'type' => 'paragraph',
                'data' => [
                    'text' => $faker->sentences($faker->numberBetween(1, 8), true),
                ],
            ],
        ],
    ]),
]);
