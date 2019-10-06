<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\User;
use Illuminate\Support\Str;
use Faker\Generator as Faker;

$randomGenders = [
    'Man',
    'Vrouw',
    'Onbekend',
    'Apache Gevechtshelikopter',
    'Aggressieve wasbeer',
];

$factory->define(User::class, function (Faker $faker) use (
    $randomGenders
) {
    return [
        'first_name' => $faker->firstName,
        'last_name' => $faker->lastName,
        'email' => $faker->unique()->safeEmail,
        'gender' => $faker->randomElement($randomGenders),
        'password' => password_hash('password', PASSWORD_DEFAULT), // password
        'remember_token' => Str::random(10),
    ];
});
