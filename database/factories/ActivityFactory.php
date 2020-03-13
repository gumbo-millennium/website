<?php

declare(strict_types=1);

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Helpers\Str;
use App\Models\Activity;
use Faker\Generator as Faker;
use Illuminate\Support\Carbon;

$imageDir = resource_path('assets/images-test');
$images = scandir($imageDir);
$imageOptions = $images === false ? collect() : collect($images)
    ->filter(static fn ($name) => Str::endsWith($name, '.jpg'))
    ->map(static fn($file) => new SplFileInfo("{$imageDir}/{$file}"));

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

    // Reschedule 20% of the activity
    if ($faker->boolean(20)) {
        $factoryData['rescheduled_from'] = $faker->dateTimeBetween(
            (clone $factoryData['start_date'])->subMonth(),
            $factoryData['start_date']
        );
        $factoryData['rescheduled_reason'] = $faker->optional(0.80)->sentence;
    }

    return $factoryData;
});
