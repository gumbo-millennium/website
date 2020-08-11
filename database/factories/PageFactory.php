<?php

declare(strict_types=1);

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Helpers\Str;
use App\Models\Page;
use App\Models\User;
use Faker\Generator as Faker;

$scandir = require __DIR__ . '/../helpers/files.php';
$imageOptions = $scandir('test-assets/images', 'jpg');

$factory->define(Page::class, static function (Faker $faker) use ($imageOptions) {
    $factoryData = [
        'title' => Str::title($faker->words($faker->randomNumber(2, 8), true)),
        'contents' => $faker->randomHtml(),
        'author_id' => optional(User::inRandomOrder()->first())->id,
        'image' => $faker->optional(0.95)->passthrough($imageOptions->random())
    ];

    // Assign image if present
    if ($factoryData['image']) {
        $factoryData['image'] = Storage::disk('public')->putFile('tests/pages', $factoryData['image']);
    }

    // Return data
    return $factoryData;
});
