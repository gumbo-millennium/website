<?php

namespace Tests\Feature\Http\Controller\EnrollNew;

use App\Facades\Enroll;
use App\Models\Activity;
use App\Models\Enrollment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
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
        $activity = factory(Activity::class)->state('with-tickets')->create();
        $ticket = $activity->tickets->first();

        $user1 = factory(User::class)->create();
        $user2 = factory(User::class)->create();
        $this->actingAs($user1);

        $enrollment = Enroll::createEnrollment($activity, $ticket);
        $this->assertInstanceOf(Enrollment::class, $enrollment);

        $this->get(route('enroll.transfer', [$activity]))
            ->assertOk();
    }
}
