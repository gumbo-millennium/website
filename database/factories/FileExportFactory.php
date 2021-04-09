<?php

declare(strict_types=1);

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\FileExport;
use App\Models\User;
use Faker\Generator as Faker;
use Illuminate\Http\File;

$factory->define(FileExport::class, static function (Faker $faker) {
    return [
        'urlkey' => $faker->uuid,
        'expires_at' => $faker->dateTimeBetween('+1 week', '+1 month'),
    ];
});

$factory->afterMaking(FileExport::class, static function (FileExport $export) {
    $fakeFile = tempnam(sys_get_temp_dir(), 'test');

    if (!file_put_contents($fakeFile, 'test')) {
        throw new RuntimeException('Failed to create test file');
    }

    if (!$export->filename) {
        $export->attachFile(new File($fakeFile));
    }

    if (!$export->owner_id) {
        $export->owner()->associate(
            User::query()->inRandomOrder()->first()
        );
    }

    return $export;
});

$factory->state(FileExport::class, 'expired', static function (Faker $faker) {
    return [
        'expires_at' => $faker->dateTimeBetween('-1 year', '-1 second'),
    ];
});
