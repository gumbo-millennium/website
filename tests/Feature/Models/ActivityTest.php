<?php

declare(strict_types=1);

namespace Tests\Feature\Models;

use App\Facades\Enroll;
use App\Models\Activity;
use App\Models\User;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Lang;
use Tests\TestCase;

class ActivityTest extends TestCase
{
    use WithFaker;

    /**
     * @dataProvider provideTicketsForPriceRange
     */
    public function test_ticket_price_range(string $expected, array ...$tickets): void
    {
        Lang::setLocale('en');
        Config::set('gumbo.transfer-fee', 0);

        /** @var Activity $activity */
        $activity = Activity::factory()->create();

        foreach ($tickets as &$ticket) {
            $ticket['title'] ??= $this->faker->sentence();
        }

        $activity->tickets()->createMany($tickets);

        $this->assertSame($expected, $activity->price_range);
    }

    public function provideTicketsForPriceRange(): array
    {
        return [
            'no tickets' => ['Price unknown'],

            'one free ticket' => [
                'Free',
                ['price' => null],
            ],
            'two free tickets' => [
                'Free',
                ['price' => null],
                ['price' => null],
            ],

            'one paid ticket' => [
                '€ 10,-',
                ['price' => 10_00],
            ],
            'two paid tickets' => [
                'From € 10,-',
                ['price' => 10_00],
                ['price' => 20_00],
            ],
            'four paid tickets' => [
                'From € 5,-',
                ['price' => 10_00],
                ['price' => 20_00],
                ['price' => 50_00],
                ['price' => 5_00],
            ],

            'one paid, one free ticket' => [
                'Free, or paid starting at € 10,-',
                ['price' => 10_00],
                ['price' => null],
            ],
        ];
    }

    public function test_is_in_future_scope(): void
    {
        [$past, $today, $future, $cancelled] = Activity::factory()->createMany([
            [
                'start_date' => Date::parse('2022-01-10T18:00'),
                'end_date' => Date::parse('2022-01-10T20:00'),
            ],
            [
                'start_date' => Date::parse('2022-01-15T18:00'),
                'end_date' => Date::parse('2022-01-15T20:00'),
            ],
            [
                'start_date' => Date::parse('2022-01-20T18:00'),
                'end_date' => Date::parse('2022-01-20T20:00'),
            ],
            [
                'start_date' => Date::parse('2022-01-25T18:00'),
                'end_date' => Date::parse('2022-01-25T20:00'),
                'cancelled_at' => Date::parse('2022-01-01'),
            ],
        ]);

        // Run query
        $activities = Activity::query()->whereInTheFuture(Date::parse('2022-01-15T15:00'))->pluck('id');

        // Ensure past and cancelled activities are not returned
        $this->assertFalse($activities->contains($past->id), 'Failed asserting that past activity is not returned');
        $this->assertFalse($activities->contains($cancelled->id), 'Failed asserting that cancelled activity is not returned');

        // Ensure today and future activities are returned
        $this->assertEquals(
            [$today->id, $future->id],
            $activities->toArray(),
            'Failed asserting that today and future activities are returned',
        );

        // Ensure null-value for whereInTheFuture is treated as today
        Date::setTestNow('2022-01-15T15:00');
        $this->assertEquals(
            $activities->toArray(),
            Activity::query()->whereInTheFuture()->pluck('id')->toArray(),
            'Failed asserting null value is treated as today',
        );
    }

    public function test_load_with_enrollments(): void
    {
        $this->markTestIncomplete("Doesn't seem to be reliable just yet");

        [$user1, $user2] = User::factory()->count(2)->create();
        $activity = Activity::factory()->withTickets()->create();
        $ticket = $activity->tickets->first();

        $this->actingAs($user1);
        $enrollment1 = Enroll::createEnrollment($activity, $ticket);

        $this->actingAs($user2);
        $enrollment2 = Enroll::createEnrollment($activity, $ticket);

        $this->assertTrue($enrollment1?->exists(), 'Failed to create enrollment for user 1');
        $this->assertTrue($enrollment2?->exists(), 'Failed to create enrollment for user 2');

        /** @var Activity $loadedActivity */
        $loadedActivity = Activity::query()
            ->withEnrollmentsFor($user1)
            ->find($activity->id);

        // Check if properly found
        $this->assertNotNull($loadedActivity);
        $this->assertTrue($loadedActivity->relationLoaded('enrollments'));

        // Check if enrollment count is right
        $this->assertCount(1, $loadedActivity->enrollments);

        // Check if enrollment list is right
        $this->assertEquals([
            $enrollment1->id,
        ], $loadedActivity->enrollments->pluck('id')->all());
    }
}
