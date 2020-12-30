<?php

declare(strict_types=1);

use Faker\Generator as Faker;

$getBlock = static function (Faker $faker): array {
    switch ($faker->randomDigit) {
        case 1:
            return [
                'type' => 'header',
                'data' => [
                    'text' => $faker->sentence,
                    'level' => $faker->numberBetween(1, 5),
                ],
            ];
        case 2:
            return [
                'type' => 'list',
                'data' => [
                    'style' => $faker->randomElement(['ordered', 'unordered']),
                    'items' => $faker->sentences($faker->numberBetween(1, 5)),
                ],
            ];
        case 3:
            return [
                'type' => 'delimiter',
                'data' => [],
            ];
        case 3:
            return [
                'type' => 'image',
                'data' => [
                    "file" => [
                        "url" => "https://picsum.photos/200/300",
                    ],
                    "caption" => $faker->optional(0.8)->sentence,
                    "withBorder" => $faker->boolean,
                    "stretched" => $faker->boolean,
                    "withBackground" => $faker->boolean,
                ],
            ];
        default:
            return [
                'type' => 'paragraph',
                'data' => [
                    'text' => $faker->sentences($faker->numberBetween(1, 8), true),
                ],
            ];
    }
};

return static function (Faker $faker) use ($getBlock) {
    // prep array
    $result = [
        'time' => $faker->dateTime()->getTimestamp(),
        'blocks' => [],
        'version' => '2.15.0',
    ];

    // determine count
    $count  = $faker->numberBetween(2, 15);

    // make blocks
    for ($i = 0; $i < $count; $i++) {
        $result['blocks'][] = $getBlock($faker);
    }

    // return
    return $result;
};
