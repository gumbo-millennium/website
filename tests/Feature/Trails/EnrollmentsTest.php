<?php

declare(strict_types=1);

namespace Tests\Feature\Trails;

use App\Helpers\Str;
use App\Models\Activity;
use App\Models\States\Enrollment as States;
use App\Models\Ticket;
use App\Models\User;
use Tests\TestCase;

/**
 * Test a full happy path of the enrollment process.
 */
class EnrollmentsTest extends TestCase
{
    public function test_enrollments_trail()
    {
        /** @var Activity $activity */
        $activity = factory(Activity::class)->states([
            'with-seats',
            'with-form',
            'public',
        ])->create();

        /** @var Ticket $firstTicket */
        [$firstTicket, $secondTicket] = $activity->tickets()->createMany([
            [
                'title' => 'Happy Trails Ticket',
                'price' => 25_00,
            ],
            [
                'title' => 'More Expensive Trail Ticket',
                'price' => 35_00,
            ],
        ]);

        // Check out the activity
        $this->get(route('activity.show', [$activity]))
            ->assertOk()
            ->assertSee($activity->title)
            ->assertSee(__('From :price', ['price' => Str::price($firstTicket->total_price)]));

        // Prep to enroll, which should require a login
        $this->get(route('enroll.create', [$activity]))
            ->assertRedirect(route('login'));

        // Login
        $user = factory(User::class)->create();
        $this->actingAs($user);

        // Try to enroll again
        $this->get(route('enroll.create', [$activity]))
            ->assertOk()
            ->assertSee($activity->title)
            ->assertSee($firstTicket->title)
            ->assertSee($secondTicket->title);

        // Save the enrollment
        $this->post(route('enroll.store', [$activity]), [
            'ticket_id' => $firstTicket->id,
        ])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('enroll.form', [$activity]));

        // Ensure the enrollment was created
        $enrollment = $activity->enrollments()->with(['user', 'ticket'])->first();
        $this->assertNotNull($enrollment);

        $this->assertTrue($user->is($enrollment->user));
        $this->assertTrue($firstTicket->is($enrollment->ticket));

        $this->assertInstanceOf(States\Created::class, $enrollment->state);
        $this->assertSame($firstTicket->total_price, $enrollment->price);
    }
}
