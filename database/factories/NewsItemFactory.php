<?php

declare(strict_types=1);

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Helpers\Str;
use App\Models\NewsItem;
use App\Models\User;
use Faker\Generator as Faker;

$scandir = require __DIR__ . '/../helpers/files.php';
$fakeEditorJs = require __DIR__ . '/../helpers/editorjs.php';
$imageOptions = $scandir('assets/images-test', 'jpg');

$factory->define(NewsItem::class, static fn (Faker $faker) => [
        'title' => Str::title($faker->words($faker->numberBetween(2, 8), true)),
        'contents' => json_encode($fakeEditorJs($faker)),
        'author_id' => optional(User::inRandomOrder()->first())->id,
        'sponsor' => $faker->optional(0.1)->company,
        'category' => $faker->randomElement(config('gumbo.news-categories')),
        'image' => $faker->optional(0.95)->passthrough($imageOptions->random())
    ]);
