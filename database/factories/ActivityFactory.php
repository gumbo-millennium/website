<?php

declare(strict_types=1);

use App\Models\Activity;
use Faker\Generator as Faker;
use Illuminate\Support\Carbon;

$scandir = require __DIR__ . '/../helpers/files.php';
$imageOptions = $scandir('test-assets/images', 'jpg');

$factory->define(Activity::class, static function (Faker $faker) use ($imageOptions) {
    $eventStart = $faker->dateTimeBetween(today()->subMonths(3), today()->addYear(1));
    $eventStartCarbon = Carbon::instance($eventStart)->toImmutable();

    $eventEnd = $faker->dateTimeBetween($eventStartCarbon->addHours(2), $eventStartCarbon->addHours(8));
    $eventEndCarbon = Carbon::instance($eventEnd)->toImmutable();

    $enrollStart = $faker->dateTimeBetween($eventStartCarbon->subWeeks(4), $eventStartCarbon);
    $enrollStartCarbon = Carbon::instance($enrollStart)->toImmutable();

    $enrollEnd = $faker->dateTimeBetween($eventStartCarbon->addHours(1), $eventEndCarbon);
    $enrollEndCarbon = Carbon::instance($enrollEnd)->toImmutable();

    $factoryData = [
        // Sometimes add a publish date
        'published_at' => $faker->optional()->dateTimeBetween('-1 year', '-5 minutes'),

        // Labels
        'name' => $faker->words(4, true),
        'tagline' => $faker->sentence($faker->numberBetween(3, 8)),

        // Dates
        'start_date' => $eventStartCarbon,
        'end_date' => $eventEndCarbon,
        'enrollment_start' => $enrollStartCarbon,
        'enrollment_end' => $enrollEndCarbon,

        // Location
        'location' => $faker->company,
        'location_address' => $faker->address,
        'location_type' => $faker->optional(0.5, Activity::LOCATION_OFFLINE)->randomElement([
            Activity::LOCATION_OFFLINE,
            Activity::LOCATION_ONLINE,
            Activity::LOCATION_MIXED,
        ]),

        // Pricing
        'price' => null,
        'member_discount' => null,
        'discount_count' => null,

        // Image
        'image' => $faker->optional(0.2)->passthrough($imageOptions->random()),
    ];

    return $factoryData;
});

$factory->state(Activity::class, 'cancelled', fn (Faker $faker) => [
    'cancelled_at' => $faker->dateTimeBetween('-1 month', 'now'),
]);

$factory->state(Activity::class, 'with-seats', fn (Faker $faker) => [
    'seats' => $faker->numberBetween(4, 80),
]);

$factory->state(Activity::class, 'public', fn () => [
    'is_public' => true,
]);

$factory->state(Activity::class, 'private', fn () => [
    'is_public' => false,
]);

$factory->state(Activity::class, 'postponed', fn (Faker $faker) => [
    'postponed_at' => $faker->dateTimeBetween('-2 weeks', '+2 weeks'),
    'postponed_reason' => $faker->optional(0.80)->sentence,
]);

$factory->state(Activity::class, 'unpublished', static fn (Faker $faker) => [
    'published_at' => $faker->dateTimeBetween('+1 minute', '+4 weeks'),
]);

$factory->state(Activity::class, 'paid', fn (Faker $faker) => [
        'price' => intdiv($faker->numberBetween(500, 6000), 25) * 25,
]);

$factory->afterMakingState(Activity::class, 'rescheduled', fn (Activity $activity, Faker $faker) => [
    'rescheduled_from' => $faker->dateTimeBetween(
        (clone $activity->start_date)->subMonth(),
        $activity->start_date,
    ),
    'rescheduled_reason' => $faker->optional(0.80)->sentence,
]);
