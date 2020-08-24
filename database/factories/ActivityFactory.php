<?php

declare(strict_types=1);

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\Activity;
use Carbon\CarbonImmutable;
use Faker\Generator as Faker;
use Illuminate\Support\Facades\Storage;

$scandir = require __DIR__ . '/../helpers/files.php';
$imageOptions = $scandir('test-assets/images', 'jpg');

$factory->define(Activity::class, static function (Faker $faker) {
    $eventStart = CarbonImmutable::instance($faker->dateTimeBetween(
        today()->subMonths(3),
        today()->addYear(1)
    ));

    $eventEnd = CarbonImmutable::instance($faker->dateTimeBetween(
        $eventStart->addHours(2),
        $eventStart->addHours(8)
    ));

    $enrollStart = CarbonImmutable::instance($faker->dateTimeBetween(
        $eventStart->subWeeks(4),
        $eventStart
    ));

    $enrollEnd = CarbonImmutable::instance($faker->dateTimeBetween(
        $eventStart->addHours(1),
        $eventEnd
    ));

    return [
        // Optionally cancel it
        'cancelled_at' => $faker->optional(0.05)->dateTimeBetween('-2 years', '-6 hours'),

        // Sometimes add a publish date
        'published_at' => $faker->optional()->dateTimeBetween('-1 year', '-5 minutes'),

        // Labels
        'name' => $faker->words(4, true),
        'tagline' => $faker->sentence($faker->numberBetween(3, 8)),

        // Dates
        'start_date' => $eventStart,
        'end_date' => $eventEnd,
        'enrollment_start' => $enrollStart,
        'enrollment_end' => $enrollEnd,

        // Location
        'location' => $faker->company,
        'location_address' => $faker->address,
        'location_type' => $faker->optional(0.5, Activity::LOCATION_OFFLINE)->randomElement([
            Activity::LOCATION_OFFLINE,
            Activity::LOCATION_ONLINE,
            Activity::LOCATION_MIXED,
        ]),

        // Seats
        'seats' => $seats = $faker->optional(0.2)->numberBetween(4, 60),
        'discount_count' => $seats ? $faker->optional(0.5)->numberBetween(2, $seats) : null,
        'is_public' => $faker->boolean(90),

        // Pricing
        'price' => null,
        'member_discount' => null,
        'discount_count' => null,
    ];
});

$factory->state(Activity::class, 'image', [
    'image' => Storage::disk('public')->putFile('tests/activities', $imageOptions->random())
]);

$factory->state(Activity::class, 'paid', static fn ($faker) => [
    'price' => $faker->numberBetween(5_00, 60_00)
]);

$factory->state(Activity::class, 'member-free', static fn ($faker, $activity) => [
    'member_discount' => $activity->price
]);

$factory->state(Activity::class, 'member-discount', static fn ($faker, $activity) => [
    'member_discount' => intdiv($faker->numberBetween(0, $activity->price), 25) * 25
]);

$factory->state(Activity::class, 'postponed', static fn ($faker) => [
    'postponed_at' => $faker->dateTimeBetween('-2 weeks', '+2 weeks'),
    'postponed_reason' => $faker->optional(0.80)->sentence,
]);

$factory->state(Activity::class, 'rescheduled', static fn ($faker, $activity) => [
    'rescheduled_from' => $faker->dateTimeBetween(
        (clone $activity->start_date)->subMonth(),
        $activity->start_date
    ),
    'rescheduled_reason' => $faker->optional(0.80)->sentence
]);

$factory->state(Activity::class, 'unpublished', static fn (Faker $faker) => [
    'published_at' => $faker->dateTimeBetween('+1 minute', '+4 weeks')
]);
