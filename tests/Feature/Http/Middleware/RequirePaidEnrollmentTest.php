<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Middleware;

use App\Facades\Enroll;
use App\Models\Activity;
use App\Models\User;
use Tests\TestCase;

class RequirePaidEnrollmentTest extends TestCase
{
    /**
     * Test the middleware.
     */
    public function test_example()
    {
        $activity = factory(Activity::class)->create();
        $ticket = $activity->tickets()->create([
            'title' => 'Free test',
        ]);

        $this->get(route('test.paid-enrollment-middleware', [$activity]))
            ->assertRedirect();

        $user = factory(User::class)->create();
        $this->actingAs($user);

        $this->get(route('test.paid-enrollment-middleware', [$activity]))
            ->assertRedirect();

        $enrollment = Enroll::createEnrollment($activity, $ticket);
        $this->assertNotNull($enrollment);

        $this->get(route('test.paid-enrollment-middleware', [$activity]))
            ->assertRedirect();

        $enrollment->price = 50_00;
        $enrollment->save();

        $this->get(route('test.paid-enrollment-middleware', [$activity]))
            ->assertOk();
    }
}
