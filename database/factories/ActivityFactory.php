<?php

declare(strict_types=1);

/** @var \Illuminate\Database\Eloquent\Factory $factory */

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
        // Optionally cancel it
        'cancelled_at' => $faker->optional(0.05)->dateTimeBetween('-2 years', '-6 hours'),

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

        // Seats
        'seats' => $faker->optional(0.2)->numberBetween(4, 60),
        'is_public' => $faker->boolean(90),

        // Pricing
        'price' => null,
        'member_discount' => null,
        'discount_count' => null,

        // Image
        'image' => $faker->optional(0.2)->passthrough($imageOptions->random())
    ];

    // Does this activity has a price?
    if ($faker->boolean(80)) {
        $price = $faker->numberBetween(500, $faker->numberBetween(500, 6000) * 1.25);
        $price = ($price - ($price % 25));
        $factoryData['price'] = $price;

        if ($faker->boolean(10)) {
            // Full discount for members
            $factoryData['member_discount'] = $price;
        } elseif ($faker->boolean(35)) {
            // Partial discount
            $factoryData['member_discount'] = $faker->numberBetween(0, $price);
            $factoryData['member_discount'] -= $factoryData['member_discount'] % 25;
        }

        // Restrict discount
        if ($factoryData['member_discount'] && $faker->boolean(25)) {
            $factoryData['discount_count'] = $faker->numberBetween(1, $factoryData['seats']);
        }
    }

    // Postpone or reschedule 20% of the activity
    if ($faker->boolean(20)) {
        // Postpone activity
        if ($faker->boolean) {
            $factoryData['postponed_at'] = $faker->dateTimeBetween('-2 weeks', '+2 weeks');
            $factoryData['postponed_reason'] = $faker->optional(0.80)->sentence;
        } else {
            $factoryData['rescheduled_from'] = $faker->dateTimeBetween(
                (clone $factoryData['start_date'])->subMonth(),
                $factoryData['start_date']
            );
            $factoryData['rescheduled_reason'] = $faker->optional(0.80)->sentence;
        }
    }

    return $factoryData;
});

$factory->state(Activity::class, 'unpublished', static fn (Faker $faker) => [
    'published_at' => $faker->dateTimeBetween('+1 minute', '+4 weeks')
]);
