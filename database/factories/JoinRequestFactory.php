<?php

use Faker\Generator as Faker;

$factory->define(App\JoinRequest::class, function (Faker $faker) {
    // Random user
    $randomUser = User::random()->first();

    // Data
    return [
        // Address
        'address' => $faker->optional()->streetAddress,
        'number' => $faker->optional()->buildingNumber,
        'city' => $faker->optional()->city,
        'zipcode' => $faker->optional()->regexify('/^[\d]{4} [A-Z]{2}$/'),
        'country' => $faker->optional()->country,

        // Phone
        'phone' => $faker->optional()->phoneNumber,
        'date-of-birth' => $faker->optional()->date,

        // User
        $user => $faker->optional()->passthrough($user),
    ];
});
