<?php

use Faker\Generator as Faker;

$factory->define(App\User::class, function (Faker $faker) {
    return [
        'first_name' => $faker->firstName,
        'last_name' => $faker->lastName,
        'email' => $faker->unique()->safeEmail,
        'password' => password_hash('secret', PASSWORD_DEFAULT), // secret
        'remember_token' => str_random(10),
    ];
});
