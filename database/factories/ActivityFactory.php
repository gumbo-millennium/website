<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Helpers\Str;
use App\Models\Activity;
use App\Models\Ticket;
use Database\Factories\Traits\HasFileFinder;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Storage;

class ActivityFactory extends Factory
{
    use HasFileFinder;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $eventStart = $this->faker->dateTimeBetween(today()->addDay(1), today()->addYear(1));
        $eventStartCarbon = Date::instance($eventStart)->toImmutable();

        $eventEnd = $this->faker->dateTimeBetween($eventStartCarbon->addHours(2), $eventStartCarbon->addHours(8));
        $eventEndCarbon = Date::instance($eventEnd)->toImmutable();

        $enrollStart = $this->faker->dateTimeBetween($eventStartCarbon->subWeeks(4), $eventStartCarbon);
        $enrollStartCarbon = Date::instance($enrollStart)->toImmutable();

        $enrollEnd = $this->faker->dateTimeBetween($eventStartCarbon->addHours(1), $eventEndCarbon);
        $enrollEndCarbon = Date::instance($enrollEnd)->toImmutable();

        return [
            // Sometimes add a publish date
            'published_at' => $this->faker->optional()->dateTimeBetween('-1 year', '-5 minutes'),

            // Labels
            'name' => $this->faker->words(4, true),
            'tagline' => $this->faker->sentence($this->faker->numberBetween(3, 8)),

            // Dates
            'start_date' => $eventStartCarbon,
            'end_date' => $eventEndCarbon,
            'enrollment_start' => $enrollStartCarbon,
            'enrollment_end' => $enrollEndCarbon,

            // Mark public by default
            'is_public' => true,

            // Location
            'location' => $this->faker->company,
            'location_address' => $this->faker->randomElement([$this->faker->address, $this->faker->url]),
        ];
    }

    public function cancelled(): self
    {
        return $this->state([
            'cancelled_at' => $this->faker->dateTimeBetween('-1 month', 'now'),
        ]);
    }

    public function withSeats(): self
    {
        return $this->state([
            'seats' => $this->faker->numberBetween(4, 80),
        ]);
    }

    public function public(): self
    {
        return $this->state([
            'is_public' => true,
        ]);
    }

    public function private(): self
    {
        return $this->state([
            'is_public' => false,
        ]);
    }

    public function postponed(): self
    {
        return $this->state([
            'postponed_at' => $this->faker->dateTimeBetween('-2 weeks', '+2 weeks'),
            'postponed_reason' => $this->faker->optional(0.80)->sentence,
        ]);
    }

    public function unpublished(): self
    {
        return $this->state([
            'published_at' => $this->faker->dateTimeBetween('+1 minute', '+4 weeks'),
        ]);
    }

    public function withForm(): self
    {
        return $this->state(function () {
            $fieldCount = $this->faker->numberBetween(1, 5);

            $fields = [];
            for ($i = 0; $i < $fieldCount; $i++) {
                $layout = $this->faker->randomElement([
                    'text-field',
                    'email',
                    'phone',
                    'content',
                ]);

                $attributes = [
                    'help' => $this->faker->optional()->sentence(),
                    'label' => $this->faker->sentence(),
                    'required' => $this->faker->boolean(),
                ];

                if ($layout === 'content') {
                    $attributes = [
                        'title' => $this->faker->sentence(),
                        'content' => $this->faker->paragraphs(3, true),
                    ];
                }

                $fields[] = [
                    'key' => Str::random(16),
                    'layout' => $layout,
                    'attributes' => $attributes,
                ];
            }

            return [
                'enrollment_questions' => $fields,
            ];
        });
    }

    public function rescheduled(): self
    {
        return $this->afterMaking(function (Activity $activity) {
            $activity->rescheduled_from = $this->faker->dateTimeBetween(
                (clone $activity->start_date)->subMonth(),
                $activity->start_date,
            );
            $activity->rescheduled_reason = $this->faker->optional(0.80)->sentence;
        });
    }

    public function withImage(): self
    {
        return $this->afterMaking(function (Activity $activity) {
            $activity->poster = Storage::disk('public')->putFile('seeded/activities/', $this->findImages('test-assets/images')->random());
        });
    }

    public function withTickets(): self
    {
        return $this->afterCreating(function (Activity $activity) {
            $activity->tickets()->saveMany([
                Ticket::factory()->make(),
                Ticket::factory()->private()->make(),
            ]);
        });
    }
}
