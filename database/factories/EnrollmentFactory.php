<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\Activity;
use App\Models\Enrollment;
use App\Models\User;
use Faker\Generator as Faker;

$factory->define(Enrollment::class, function (Faker $faker) {
    $activity = Activity::inRandomOrder()->first();
    $user = User::inRandomOrder()->first();
    return [
        'user_id' => $user->id,
        'user_type' => $user->hasRole('member') ? 'member' : 'guest',
        'activity_id' => $activity->id,
        'price' => $faker->boolean ? $activity->price : ($activity->price - $activity->member_discount),
    ];
});
