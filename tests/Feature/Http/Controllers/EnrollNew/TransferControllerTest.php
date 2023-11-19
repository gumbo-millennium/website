<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\EnrollNew;

use App\Facades\Enroll;
use App\Models\Activity;
use App\Models\Enrollment;
use App\Models\User;
use Tests\TestCase;

class TransferControllerTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_regular_call()
    {
        $activity = Activity::factory()->withTickets()->create();
        $ticket = $activity->tickets->first();

        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $this->actingAs($user1);

        $enrollment = Enroll::createEnrollment($activity, $ticket);
        $this->assertInstanceOf(Enrollment::class, $enrollment);

        $this->get(route('enroll.transfer', [$activity]))
            ->assertOk();
    }
}
