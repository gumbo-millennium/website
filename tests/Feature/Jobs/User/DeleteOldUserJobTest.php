<?php

declare(strict_types=1);

namespace Tests\Feature\Jobs\User;

use App\Facades\Enroll;
use App\Jobs\User\DeleteOldUserJob;
use App\Models\Activity;
use App\Models\States\Enrollment\Cancelled;
use App\Models\States\Enrollment\Confirmed;
use App\Models\User;
use Illuminate\Support\Facades\App;
use Tests\TestCase;

class DeleteOldUserJobTest extends TestCase
{
    public function test_simple_user_delete(): void
    {
        $user = $this->user();

        $this->assertTrue($user->exists);
        $this->assertFalse($user->trashed());

        DeleteOldUserJob::dispatch($user);

        $user->refresh();

        $this->assertFalse($user->exists);
    }

    public function test_superuser_delete(): void
    {
        $user = $this->user();
        $user->givePermissionTo('super-admin');
        $user->save();

        $this->assertFalse($this->getCanBeDeleted($user));

        DeleteOldUserJob::dispatch($user);

        $user->refresh();

        $this->assertTrue($user->exists);
        $this->assertFalse($user->trashed());
    }

    public function test_old_user_is_not_deleted_if_it_has_future_enrollments(): void
    {
        $user = $this->user();

        $activity = Activity::factory()->withTickets()->create();
        [$ticket] = $activity->tickets;

        $this->actingAs($user);
        $enrollment = Enroll::createEnrollment($activity, $ticket);

        // Before
        $this->assertDatabaseHas($enrollment->getTable(), ['id' => $enrollment->id, 'user_id' => $user->id]);

        DeleteOldUserJob::dispatch($user);

        // After
        $this->assertNotNull($user->fresh());
        $this->assertDatabaseHas($enrollment->getTable(), ['id' => $enrollment->id, 'user_id' => $user->id]);
    }

    public function test_old_user_is_not_deleted_if_it_has_future_stable_enrollments(): void
    {
        $user = $this->user();

        $activity = Activity::factory()->withTickets()->create();
        [$ticket] = $activity->tickets;

        $this->actingAs($user);
        $enrollment = Enroll::createEnrollment($activity, $ticket);
        $enrollment->state->transitionTo(Confirmed::class);
        $enrollment->save();

        // Before
        $this->assertDatabaseHas($enrollment->getTable(), ['id' => $enrollment->id, 'user_id' => $user->id]);

        DeleteOldUserJob::dispatch($user);

        // After
        $this->assertNotNull($user->fresh());
        $this->assertDatabaseHas($enrollment->getTable(), ['id' => $enrollment->id, 'user_id' => $user->id]);
    }

    public function test_old_user_is_deleted_if_future_enrollments_are_cancelled(): void
    {
        $user = $this->user();

        $activity = Activity::factory()->withTickets()->create();
        [$ticket] = $activity->tickets;

        $this->actingAs($user);
        $enrollment = Enroll::createEnrollment($activity, $ticket);
        $enrollment->state->transitionTo(Cancelled::class);
        $enrollment->save();

        // Before
        $this->assertDatabaseHas($enrollment->getTable(), ['id' => $enrollment->id, 'user_id' => $user->id]);

        DeleteOldUserJob::dispatch($user);

        // After
        $this->assertNull($user->fresh());
        $this->assertDatabaseMissing($enrollment->getTable(), ['id' => $enrollment->id, 'user_id' => $user->id]);
    }

    private function user(array $props = []): User
    {
        return User::factory()->create($props);
    }

    private function getCanBeDeleted(User $user): bool
    {
        return App::make(DeleteOldUserJob::class, [$user])->canBeDeleted();
    }
}
