<?php

declare(strict_types=1);

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Helpers\Str;
use App\Models\NewsItem;
use App\Models\User;
use Faker\Generator as Faker;

$scandir = require __DIR__ . '/../helpers/files.php';
$fakeEditorJs = require __DIR__ . '/../helpers/editorjs.php';
$imageOptions = $scandir('test-assets/images', 'jpg');

$factory->define(NewsItem::class, static function (Faker $faker) use ($fakeEditorJs, $imageOptions) {
    // Data
    $factoryData = [
        'title' => Str::title($faker->words($faker->numberBetween(2, 8), true)),
        'contents' => json_encode($fakeEditorJs($faker)),
        'author_id' => optional(User::inRandomOrder()->first())->id,
        'sponsor' => $faker->optional(0.1)->company,
        'category' => $faker->randomElement(config('gumbo.news-categories')),
        'image' => $faker->optional(0.95)->passthrough($imageOptions->random())
    ];

    // Assign image if present
    if ($factoryData['image']) {
        $factoryData['image'] = Storage::disk('public')->putFile('tests/news', $factoryData['image']);
    }

    // Return data
    return $factoryData;
});
