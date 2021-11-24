<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Middleware;

use App\Facades\Enroll;
use App\Models\Activity;
use App\Models\States\Enrollment as States;
use App\Models\User;
use Tests\TestCase;

class RequireActiveEnrollmentTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_middleware(): void
    {
        $activity = factory(Activity::class)->create();
        $ticket = $activity->tickets()->create([
            'title' => 'Middleware test',
        ]);

        $this->get(route('test.active-enrollment-middleware', [$activity]))
            ->dump()
            ->assertRedirect();

        $user = factory(User::class)->create();
        $this->actingAs($user);

        $this->get(route('test.active-enrollment-middleware', [$activity]))
            ->assertRedirect();

        $enrollment = Enroll::createEnrollment($activity, $ticket);
        $this->assertNotNull($enrollment);

        $this->get(route('test.active-enrollment-middleware', [$activity]))
            ->assertOk();

        $enrollment->state = new States\Paid($enrollment);
        $enrollment->save();

        $this->get(route('test.active-enrollment-middleware', [$activity]))
            ->assertOk();

        $enrollment->state = new States\Cancelled($enrollment);
        $enrollment->save();

        $this->get(route('test.active-enrollment-middleware', [$activity]))
            ->assertRedirect();
    }
}
