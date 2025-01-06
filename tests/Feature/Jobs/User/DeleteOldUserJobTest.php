<?php

declare(strict_types=1);

namespace Tests\Feature\Jobs\User;

use App\Facades\Enroll;
use App\Jobs\User\DeleteOldUserJob;
use App\Models\Activity;
use App\Models\User;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Date;
use Tests\TestCase;

class DeleteOldUserJobTest extends TestCase
{
    public function test_simple_user_delete(): void
    {
        $user = $this->user();

        $this->assertTrue($user->exists);
        $this->assertFalse($user->trashed());

        DeleteOldUserJob::dispatch($user);

        $this->assertNull($user->fresh());
    }

    public function test_superuser_delete(): void
    {
        $user = $this->user();
        $user->assignRole('board');
        $user->save();

        $this->assertFalse($this->getCanBeDeleted($user));

        DeleteOldUserJob::dispatch($user);

        $this->assertNotNull($user->fresh());
    }

    public function test_delete_with_future_enrollments(): void
    {
        $user = $this->user();
        $this->actingAs($user);

        $activity = Activity::factory()->withTickets()->create([
            'start_date' => Date::now()->addDay(),
            'end_date' => Date::now()->addDay()->addHours(4),
        ]);
        $enrollment = Enroll::createEnrollment($activity, $activity->tickets->first());

        // Before
        $this->assertDatabaseHas($enrollment->getTable(), ['id' => $enrollment->id, 'user_id' => $user->id]);

        DeleteOldUserJob::dispatch($user);

        // After
        $this->assertNotNull($user->fresh());
        $this->assertDatabaseHas($enrollment->getTable(), ['id' => $enrollment->id, 'user_id' => $user->id]);
    }

    public function test_delete_with_past_enrollments(): void
    {
        $user = $this->user();

        $activity = Activity::factory()->withTickets()->create([
            'start_date' => Date::now()->addDay(),
            'end_date' => Date::now()->addDay()->addHours(4),
        ]);

        $this->actingAs($user);
        $enrollment = Enroll::createEnrollment($activity, $activity->tickets->first());

        $this->travel(7)->days();

        // Before
        $this->assertDatabaseHas($enrollment->getTable(), ['id' => $enrollment->id, 'user_id' => $user->id]);

        DeleteOldUserJob::dispatch($user);

        // After
        $this->assertNull($user->fresh());
        $this->assertDatabaseHas($enrollment->getTable(), ['id' => $enrollment->id]);
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
