<?php

declare(strict_types=1);

use App\Models\DataExport;
use Faker\Generator as Faker;
use Illuminate\Support\Facades\Storage;

$factory->define(DataExport::class, function (Faker $faker) {
    return [
        'created_at' => $faker->dateTimeBetween('-1 year', 'now'),
        'updated_at' => $faker->dateTimeBetween('-1 year', 'now'),
    ];
});

$factory->afterMakingState(DataExport::class, 'with-data', function (DataExport $dataExport, Faker $faker) {
    $path = "user-export/data/{$faker->uuid}.json";

    Storage::put($path, json_encode([
        'user-id' => optional($dataExport->user)->id,
        'name' => $faker->name,
    ]));

    $dataExport->path = $path;
    $dataExport->completed_at = $faker->dateTimeBetween('-1 year', 'now');
});

$factory->afterCreatingState(DataExport::class, 'expired', function (DataExport $dataExport, Faker $faker) {
    $dataExport->expires_at = $faker->dateTimeBetween('-1 year', '-1 day');
    $dataExport->save();
});
