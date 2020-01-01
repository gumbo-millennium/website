<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\Activity;
use Faker\Generator as Faker;
use Illuminate\Support\Carbon;

$factory->define(Activity::class, function (Faker $faker) {
    $eventStart = $faker->dateTimeBetween(today()->subMonths(3), today()->addYear(1));
    $eventStartCarbon = Carbon::instance($eventStart)->toImmutable();

    $eventEnd = $faker->dateTimeBetween($eventStartCarbon->addHours(2), $eventStartCarbon->addHours(8));
    $eventEndCarbon = Carbon::instance($eventEnd)->toImmutable();

    $enrollStart = $faker->dateTimeBetween($eventStartCarbon->subWeeks(4), $eventStartCarbon);
    $enrollStartCarbon = Carbon::instance($enrollStart)->toImmutable();

    $enrollEnd = $faker->dateTimeBetween($eventStartCarbon->addHours(1), $eventEndCarbon);
    $enrollEndCarbon = Carbon::instance($enrollEnd)->toImmutable();

    // Determine price
    $memberPrice = $guestPrice = null;
    if ($faker->boolean(0.4)) {
        $memberPrice = intdiv($faker->numberBetween(500, 6000), 25) * 25;
        $guestPrice = intdiv($faker->numberBetween(500, $memberPrice * 1.25), 25) * 25;
    } elseif ($faker->boolean(0.4)) {
        $guestPrice = intdiv($faker->numberBetween(500, 2000), 25) * 25;
    }

    // Determine seat count
    $memberSeats = $guestSeats = null;
    if ($faker->boolean(0.4)) {
        $memberSeats = $faker->numberBetween(12, 60) / 4 * 4;
        $guestSeats = $faker->numberBetween(5, floor($memberSeats * 0.8)) / 4 * 4;
    } elseif ($faker->boolean(0.4)) {
        $memberSeats = $faker->numberBetween(4, 60) / 4 * 4;
        $guestSeats = 0;
        $guestPrice = null;
    }

    return [
        // Optionally cancel it
        'cancelled_at' => $faker->optional(0.05)->dateTimeBetween('-2 years', '-6 hours'),

        // Labels
        'name' => $faker->words(4, true),
        'tagline' => $faker->sentence($faker->numberBetween(3, 8)),

        // Dates
        'start_date' => $eventStartCarbon,
        'end_date' => $eventEndCarbon,
        'enrollment_start' => $enrollStartCarbon,
        'enrollment_end' => $enrollEndCarbon,

        // Seats
        'seats' => $memberSeats,
        'is_public' => $faker->boolean(0.1),

        // Pricing
        'price_member' => $memberPrice,
        'price_guest' => $guestPrice,
    ];
});
