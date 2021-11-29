<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Middleware;

use App\Facades\Enroll;
use App\Http\Middleware\RequireActiveEnrollment;
use App\Http\Middleware\RequirePaidEnrollment;
use App\Models\Activity;
use App\Models\User;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class RequirePaidEnrollmentTest extends TestCase
{
    /**
     * Test the middleware.
     */
    public function test_example()
    {
        Route::get('/test/middleware/{activity}', fn (Activity $activity) => 'OK')
            ->middleware([
                RequireActiveEnrollment::class,
                RequirePaidEnrollment::class,
            ]);

        $activity = factory(Activity::class)->create();
        $ticket = $activity->tickets()->create([
            'title' => 'Free test',
        ]);

        $this->get("/test/middleware/{$activity->getRouteKey()}")
            ->assertRedirect();

        $user = factory(User::class)->create();
        $this->actingAs($user);

        $this->get("/test/middleware/{$activity->getRouteKey()}")
            ->assertRedirect();

        $enrollment = Enroll::createEnrollment($activity, $ticket);
        $this->assertNotNull($enrollment);

        $this->get("/test/middleware/{$activity->getRouteKey()}")
            ->assertRedirect();

        $enrollment->price = 50_00;
        $enrollment->save();

        $this->get("/test/middleware/{$activity->getRouteKey()}")
            ->assertOk();
    }
}
