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

$factory->afterMakingState(DataExport::class, 'with_data', function (DataExport $dataExport, Faker $faker) {
    $dataExport->path = Storage::put("user-export/data/{$faker->uuid}.json", json_encode([
        'user-id' => $faker->id,
        'name' => $faker->name,
    ]));
});

$factory->afterCreatingState(DataExport::class, 'expired', function (DataExport $dataExport, Faker $faker) {
    $dataExport->expires_at = $faker->dateTimeBetween('-1 year', '-1 day');
    $dataExport->save();
});
