<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\Activity;
use App\Models\Enrollment;
use App\Models\User;
use Faker\Generator as Faker;

$factory->define(Enrollment::class, function (Faker $faker) {
    $activity = Activity::inRandomOrder()->first();
    return [
        'user_id' => User::inRandomOrder()->first()->id,
        'activity_id' => $activity->id,
        'price' => $faker->boolean ? $activity->price_member : $activity->price_guest,
    ];
});
