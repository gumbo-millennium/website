<?php

declare(strict_types=1);

use App\Enums\AlbumVisibility;
use App\Models\PhotoAlbum;
use Faker\Generator as Faker;

$factory->define(PhotoAlbum::class, fn (Faker $faker) => [
    'name' => $faker->sentence,
    'published_at' => $faker->dateTimeBetween('-1 year', 'now'),
]);

$factory->state(PhotoAlbum::class, 'public', fn () => [
    'visibility' => AlbumVisibility::WORLD,
]);

$factory->state(PhotoAlbum::class, 'users', fn () => [
    'visibility' => AlbumVisibility::USERS,
]);

$factory->state(PhotoAlbum::class, 'members', fn () => [
    'visibility' => AlbumVisibility::MEMBERS_ONLY,
]);
