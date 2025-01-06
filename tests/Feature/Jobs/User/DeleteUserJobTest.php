<?php

declare(strict_types=1);

namespace Tests\Feature\Jobs\User;

use App\Facades\Enroll;
use App\Jobs\User\DeleteUserJob;
use App\Models\Activity;
use App\Models\User;
use Illuminate\Support\Facades\App;
use Tests\TestCase;

class DeleteUserJobTest extends TestCase
{
    public function test_simple_user_delete(): void
    {
        $user = $this->user();

        $this->assertTrue($user->exists);
        $this->assertFalse($user->trashed());

        DeleteUserJob::dispatch($user);

        $user->refresh();

        $this->assertTrue($user->exists);
        $this->assertTrue($user->trashed());
    }

    public function test_superuser_delete(): void
    {
        $user = $this->user();
        $user->givePermissionTo('super-admin');
        $user->save();

        $this->assertFalse($this->getCanBeDeleted($user));

        DeleteUserJob::dispatch($user);

        $this->assertFalse($user->refresh()->trashed());
    }

    public function test_users_are_unenrolled(): void
    {
        $user = $this->user();

        $activity = Activity::factory()->withTickets()->create();
        [$ticket] = $activity->tickets;

        $this->actingAs($user);
        $enrollment = Enroll::createEnrollment($activity, $ticket);

        $this->assertDatabaseHas('enrollments', ['id' => $enrollment->id]);
    }

    private function user(array $props = []): User
    {
        return User::factory()->create($props);
    }

    private function getCanBeDeleted(User $user): bool
    {
        return App::make(DeleteUserJob::class, [$user])->canBeDeleted();
    }
}
