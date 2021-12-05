<?php

declare(strict_types=1);

use App\Helpers\Str;
use App\Models\NewsItem;
use App\Models\User;
use Faker\Generator as Faker;

$fakeEditorJs = require __DIR__ . '/../helpers/editorjs.php';
$factory->define(NewsItem::class, static fn (Faker $faker) => [
    'title' => Str::title($faker->words($faker->numberBetween(2, 8), true)),
    'contents' => json_encode($fakeEditorJs($faker)),
    'author_id' => optional(User::inRandomOrder()->first())->id,
    'sponsor' => $faker->optional(0.1)->company,
    'category' => $faker->randomElement(config('gumbo.news-categories')),
]);

$scandir = require __DIR__ . '/../helpers/files.php';

$imageOptions = $scandir('test-assets/images', 'jpg');
$factory->afterMakingState(NewsItem::class, 'with-image', function (NewsItem $item) use ($imageOptions) {
    $item->cover = Storage::disk('public')->putFile('tests/images', $imageOptions->random());

    return $item;
});
