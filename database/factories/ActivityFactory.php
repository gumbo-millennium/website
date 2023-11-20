<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Helpers\Str;
use App\Models\Activity;
use App\Models\Ticket;
use Database\Factories\Traits\HasEditorjs;
use Database\Factories\Traits\HasFileFinder;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Storage;

class ActivityFactory extends Factory
{
    use HasEditorjs;
    use HasFileFinder;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $eventStart = $this->faker->dateTimeBetween(Date::now()->addMonth(), Date::now()->addYear(1));
        $eventEnd = $this->faker->dateTimeBetween(
            Date::instance($eventStart)->addHours(2),
            Date::instance($eventStart)->addHours(8),
        );

        return [
            // Sometimes add a publish date
            'published_at' => $this->faker->optional()->dateTimeBetween('-1 year', '-5 minutes'),

            // Labels
            'name' => $this->faker->words(4, true),
            'tagline' => $this->faker->sentence($this->faker->numberBetween(3, 8)),

            // Dates
            'start_date' => $eventStart,
            'end_date' => $eventEnd,

            // Mark public by default
            'is_public' => true,

            // Location
            'location' => $this->faker->company,
            'location_address' => $this->faker->randomElement([$this->faker->address, $this->faker->url]),

            // Description
            'description' => $this->faker->optional()->passthrough($this->getEditorBlocks()),
            'ticket_text' => $this->faker->optional(0.25)->passthrough($this->getEditorBlocks()),
        ];
    }

    /**
     * Set when this activity takes place.
     */
    public function dates(
        DateTimeInterface|string|null $start = null,
        DateTimeInterface|string|null $end = null
    ): self {
        return $this->state(array_filter([
            'start_date' => $start ? Date::parse($start) : null,
            'end_date' => $end ? Date::parse($end) : null,
        ]));
    }

    /**
     * Set when enrollment for this activity starts and ends.
     */
    public function enrollmentDates(
        DateTimeInterface|string|null $start = null,
        DateTimeInterface|string|null $end = null,
    ): self {
        return $this->state([
            'enrollment_start' => $start ? Date::parse($start) : null,
            'enrollment_end' => $end ? Date::parse($end) : null,
        ]);
    }

    public function cancelled(): self
    {
        return $this->state([
            'cancelled_at' => $this->faker->dateTimeBetween('-1 month', 'now'),
        ]);
    }

    public function withSeats(?int $seats = null): self
    {
        return $this->state([
            'seats' => $seats ?? $this->faker->numberBetween(4, 80),
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
