<?php

declare(strict_types=1);

use App\Models\Activity;
use App\Models\Ticket;
use Faker\Generator as Faker;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

$scandir = require __DIR__ . '/../helpers/files.php';
$imageOptions = $scandir('test-assets/images', 'jpg');

$factory->define(Activity::class, static function (Faker $faker) {
    $eventStart = $faker->dateTimeBetween(today()->addDay(1), today()->addYear(1));
    $eventStartCarbon = Carbon::instance($eventStart)->toImmutable();

    $eventEnd = $faker->dateTimeBetween($eventStartCarbon->addHours(2), $eventStartCarbon->addHours(8));
    $eventEndCarbon = Carbon::instance($eventEnd)->toImmutable();

    $enrollStart = $faker->dateTimeBetween($eventStartCarbon->subWeeks(4), $eventStartCarbon);
    $enrollStartCarbon = Carbon::instance($enrollStart)->toImmutable();

    $enrollEnd = $faker->dateTimeBetween($eventStartCarbon->addHours(1), $eventEndCarbon);
    $enrollEndCarbon = Carbon::instance($enrollEnd)->toImmutable();

    return [
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

        // Mark public by default
        'is_public' => true,

        // Location
        'location' => $faker->company,
        'location_address' => $faker->randomElement([$faker->address, $faker->url]),
    ];
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

$factory->afterMakingState(Activity::class, 'with-form', function (Activity $activity, Faker $faker) {
    $fieldCount = $faker->numberBetween(1, 5);

    $fields = [];
    for ($i = 0; $i < $fieldCount; $i++) {
        $layout = $faker->randomElement([
            'text-field',
            'email',
            'phone',
            'content',
        ]);

        $attributes = [
            'help' => $faker->optional()->sentence(),
            'label' => $faker->sentence(),
            'required' => $faker->boolean(),
        ];

        if ($layout === 'content') {
            $attributes = [
                'title' => $faker->sentence(),
                'content' => $faker->paragraphs(3, true),
            ];
        }

        $fields[] = [
            'key' => Str::random(16),
            'layout' => $layout,
            'attributes' => $attributes,
        ];
    }

    $activity->enrollment_questions = $fields;
});

$factory->afterMakingState(Activity::class, 'rescheduled', fn (Activity $activity, Faker $faker) => [
    'rescheduled_from' => $faker->dateTimeBetween(
        (clone $activity->start_date)->subMonth(),
        $activity->start_date,
    ),
    'rescheduled_reason' => $faker->optional(0.80)->sentence,
]);

$factory->afterMakingState(Activity::class, 'with-image', function (Activity $activity) use ($imageOptions) {
    $activity->poster = Storage::disk('public')->putFile('seeded/activities/', $imageOptions->random());
});

$factory->afterCreatingState(Activity::class, 'with-tickets', function (Activity $activity) {
    $activity->tickets()->saveMany([
        factory(Ticket::class)->make(),
        factory(Ticket::class)->state('private')->make(),
    ]);
});
