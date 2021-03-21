<?php

declare(strict_types=1);

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\MemberReferral;
use Faker\Generator as Faker;

$factory->define(MemberReferral::class, static function (Faker $faker) {
    return [
        'subject' => $faker->firstName,
        'referred_by' => $faker->name,
    ];
});
