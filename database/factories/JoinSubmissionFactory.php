<?php

declare(strict_types=1);

use App\Models\JoinSubmission;
use Faker\Generator as Faker;

$inserts = [
    'van',
    'de',
    'van het',
    'van \'t',
    'ter',
    'van de',
];

$genders = array_merge(
    array_fill(0, 10, 'Man'),
    array_fill(0, 10, 'Vrouw'),
    array_fill(0, 2, 'Apache Gevechtshelikopter'),
    array_fill(0, 2, 'Lightrode Wasbeer'),
    array_fill(0, 4, 'Waluigi')
);

$factory->define(JoinSubmission::class, static fn (Faker $faker) => [
    // Names
    'first_name' => $faker->firstName,
    'insert' => $faker->optional(0.4)->randomElement($inserts),
    'last_name' => $faker->lastName,

    // Contact info
    'phone' => $faker->phoneNumber,
    'email' => $faker->safeEmail,

    // User details
    'date_of_birth' => $faker->dateTimeBetween('30 years ago', '16 years ago')->format('d-m-Y'),
    'gender' => $faker->randomElement($genders),

    // Contact data
    'street' => $faker->streetName,
    'number' => $faker->buildingNumber,
    'city' => $faker->city,
    'postal_code' => $faker->postcode,
    'country' => $faker->optional(0.1, 'NL')->countryCode,

    // Result
    'granted' => $faker->optional(0.15)->boolean(),
]);
