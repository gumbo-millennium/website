<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Middleware;

use App\Facades\Enroll;
use App\Http\Middleware\RequireActiveEnrollment;
use App\Models\Activity;
use App\Models\States\Enrollment as States;
use App\Models\User;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class RequireActiveEnrollmentTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_middleware(): void
    {
        Route::get('/test/middleware/{activity}', fn (Activity $activity) => 'OK')
            ->middleware([RequireActiveEnrollment::class]);

        $activity = Activity::factory()->create();
        $ticket = $activity->tickets()->create([
            'title' => 'Middleware test',
        ]);

        $this->get("/test/middleware/{$activity->getRouteKey()}")
            ->assertRedirect();

        $user = User::factory()->create();
        $this->actingAs($user);

        $this->get("/test/middleware/{$activity->getRouteKey()}")
            ->assertRedirect();

        $enrollment = Enroll::createEnrollment($activity, $ticket);
        $this->assertNotNull($enrollment);

        $this->get("/test/middleware/{$activity->getRouteKey()}")
            ->assertOk();

        $enrollment->state = new States\Paid($enrollment);
        $enrollment->save();

        $this->get("/test/middleware/{$activity->getRouteKey()}")
            ->assertOk();

        $enrollment->state = new States\Cancelled($enrollment);
        $enrollment->save();

        $this->get("/test/middleware/{$activity->getRouteKey()}")
            ->assertRedirect();
    }
}
