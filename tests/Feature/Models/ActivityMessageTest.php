<?php

declare(strict_types=1);

namespace Tests\Feature\Models;

use App\Facades\Enroll;
use App\Models\Activity;
use App\Models\ActivityMessage;
use App\Models\States\Enrollment\Cancelled;
use App\Models\States\Enrollment\Confirmed;
use App\Models\Ticket;
use App\Models\User;
use Tests\TestCase;

class ActivityMessageTest extends TestCase
{
    public function test_basic_behaviour(): void
    {
        /** @var Activity */
        $activity = Activity::factory()->create();

        /** @var Ticket */
        $ticket = Ticket::factory()->for($activity)->create();

        /** @var ActivityMessage */
        $message = ActivityMessage::factory()->for($activity)->create();

        /** @var User */
        [$randomUser, $pendingUser, $confirmedUser, $cancelledUser] = User::factory()->createMany([
            ['first_name' => 'Random', 'last_name' => 'User'],
            ['first_name' => 'Pending', 'last_name' => 'User'],
            ['first_name' => 'Confirmed', 'last_name' => 'User'],
            ['first_name' => 'Cancelled', 'last_name' => 'User'],
        ]);

        $this->actingAs($pendingUser);
        Enroll::createEnrollment($activity, $ticket);

        $this->actingAs($confirmedUser);
        $confirmedEnrollment = Enroll::createEnrollment($activity, $ticket);
        $confirmedEnrollment->state->transitionTo(Confirmed::class);

        $this->actingAs($cancelledUser);
        $cancelledEnrollment = Enroll::createEnrollment($activity, $ticket);
        $cancelledEnrollment->state->transitionTo(Cancelled::class);

        $matchedEnrollments = $message->getEnrollmentsCursor()->pluck('id')->toArray();

        $this->assertEquals([$confirmedEnrollment->id], $matchedEnrollments);
    }

    public function test_pending_behaviour()
    {
        /** @var Activity */
        $activity = Activity::factory()->create();

        /** @var Ticket */
        $ticket = Ticket::factory()->for($activity)->create();

        /** @var ActivityMessage */
        $message = ActivityMessage::factory()->for($activity)->create([
            'include_pending' => true,
        ]);

        /** @var User */
        [$randomUser, $pendingUser, $confirmedUser, $cancelledUser] = User::factory()->createMany([
            ['first_name' => 'Random', 'last_name' => 'User'],
            ['first_name' => 'Pending', 'last_name' => 'User'],
            ['first_name' => 'Confirmed', 'last_name' => 'User'],
            ['first_name' => 'Cancelled', 'last_name' => 'User'],
        ]);

        $this->actingAs($pendingUser);
        $pendingEnrollment = Enroll::createEnrollment($activity, $ticket);

        $this->actingAs($confirmedUser);
        $confirmedEnrollment = Enroll::createEnrollment($activity, $ticket);
        $confirmedEnrollment->state->transitionTo(Confirmed::class);

        $this->actingAs($cancelledUser);
        $cancelledEnrollment = Enroll::createEnrollment($activity, $ticket);
        $cancelledEnrollment->state->transitionTo(Cancelled::class);

        $matchedEnrollments = $message->getEnrollmentsCursor()->pluck('id')->toArray();

        $this->assertEquals([$pendingEnrollment->id, $confirmedEnrollment->id], $matchedEnrollments);
    }

    public function test_ticket_behaviour(): void
    {
        /** @var Activity */
        $activity = Activity::factory()->create();

        /** @var Ticket */
        [$ticket1, $ticket2] = Ticket::factory()->times(2)->for($activity)->create();

        /** @var ActivityMessage */
        $message = ActivityMessage::factory()->for($activity)->create([
            'include_pending' => true,
        ]);
        $message->tickets()->sync([$ticket1->id]);

        /** @var User */
        [$ticket1User, $ticket2User] = User::factory()->createMany([
            ['first_name' => 'Ticket 1', 'last_name' => 'User'],
            ['first_name' => 'Ticket 2', 'last_name' => 'User'],
        ]);

        $this->actingAs($ticket1User);
        $ticket1Enrollment = Enroll::createEnrollment($activity, $ticket1);
        $ticket1Enrollment->state->transitionTo(Confirmed::class);

        $this->actingAs($ticket2User);
        $ticket2Enrollment = Enroll::createEnrollment($activity, $ticket2);
        $ticket2Enrollment->state->transitionTo(Confirmed::class);

        $matchedEnrollments = $message->getEnrollmentsCursor()->pluck('id')->toArray();

        $this->assertEquals([$ticket1Enrollment->id], $matchedEnrollments);
    }
}
