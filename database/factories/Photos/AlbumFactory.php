<?php

declare(strict_types=1);

use App\Enums\AlbumVisibility;
use App\Models\Photos\Album;
use Faker\Generator as Faker;

$factory->define(Album::class, fn (Faker $faker) => [
    'name' => $faker->sentence,
    'published_at' => $faker->dateTimeBetween('-1 year', 'now'),
]);

$factory->state(Album::class, 'public', fn () => [
    'visibility' => AlbumVisibility::WORLD,
]);

$factory->state(Album::class, 'users', fn () => [
    'visibility' => AlbumVisibility::USERS,
]);

$factory->state(Album::class, 'members', fn () => [
    'visibility' => AlbumVisibility::MEMBERS_ONLY,
]);
