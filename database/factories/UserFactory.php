<?php

use Faker\Generator as Faker;

$randomGenders = [
    'Man',
    'Vrouw',
    'Onbekend',
    'Apache Gevechtshelikopter',
    'Aggressieve wasbeer',
];

$factory->define(App\User::class, function (Faker $faker) use (
    $randomGenders
) {
    return [
        'first_name' => $faker->firstName,
        'last_name' => $faker->lastName,
        'email' => $faker->unique()->safeEmail,
        'gender' => $faker->randomElement($randomGenders),
        'password' => password_hash('secret', PASSWORD_DEFAULT), // secret
        'remember_token' => str_random(10),
    ];
});
