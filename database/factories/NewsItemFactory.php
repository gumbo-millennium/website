<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Helpers\Str;
use App\Models\NewsItem;
use App\Models\User;
use Faker\Generator as Faker;

$randomEditorBlock = function (Faker $faker) {
    switch ($faker->randomDigit) {
        case 1:
            return [
                'type' => 'header',
                'data' => [
                    'text' => $faker->sentence,
                    'level' => $faker->numberBetween(1, 5)
                ]
            ];
        case 2:
            return [
                'type' => 'list',
                'data' => [
                    'style' => $faker->randomElement(['ordered', 'unordered']),
                    'items' => $faker->sentences($faker->numberBetween(1, 5))
                ]
            ];
        case 3:
            return [
                'type' => 'delimiter',
                'data' => []
            ];
        case 3:
            return [
                'type' => 'image',
                'data' => [
                    "file" => [
                        "url" => "https://picsum.photos/200/300"
                    ],
                    "caption" => $faker->optional(0.8)->sentence,
                    "withBorder" => $faker->boolean,
                    "stretched" => $faker->boolean,
                    "withBackground" => $faker->boolean
                ]
            ];
        default:
            return [
                'type' => 'paragraph',
                'data' => [
                    'text' => $faker->sentences($faker->numberBetween(1, 8), true)
                ]
            ];
    }
};

$fakeEditorJs = function (Faker $faker) use ($randomEditorBlock) {
    // prep array
    $result = [
        'time' => $faker->dateTime()->getTimestamp(),
        'blocks' => [],
        'version' => '2.15.0'
    ];

    // determine count
    $count  = $faker->numberBetween(2, 15);

    // make blocks
    for ($i = 0; $i < $count; $i++) {
        $result['blocks'][] = $randomEditorBlock($faker);
    }

    // return
    return $result;
};

$factory->define(NewsItem::class, function (Faker $faker) use ($fakeEditorJs) {
    return [
        'title' => Str::title($faker->words($faker->numberBetween(2, 8), true)),
        'contents' => json_encode($fakeEditorJs($faker)),
        'author_id' => optional(User::inRandomOrder()->first())->id,
        'sponsor' => $faker->optional(0.1)->company
    ];
});
