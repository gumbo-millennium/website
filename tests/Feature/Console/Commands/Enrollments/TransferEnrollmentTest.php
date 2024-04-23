<?php

declare(strict_types=1);

namespace Tests\Feature\Console\Commands\Enrollments;

use App\Facades\Enroll;
use App\Models\Activity;
use App\Models\Enrollment;
use App\Models\States\Enrollment as EnrollmentStates;
use App\Models\User;
use App\Notifications\EnrollmentTransferred;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class TransferEnrollmentTest extends TestCase
{
    /**
     * Run a happy test.
     */
    public function test_happy_trail(): void
    {
        [$admin, $sender, $recipient] = User::factory()->count(3)->create();
        Config::set('gumbo.admin_id', $admin->id);

        $activity = Activity::factory()->withTickets()->create();
        $ticket = $activity->tickets->first();

        $this->actingAs($sender);

        /** @var Enrollment $enrollment */
        $enrollment = Enroll::createEnrollment($activity, $ticket);
        $enrollment->state->transitionTo(EnrollmentStates\Confirmed::class);
        $enrollment->save();

        Notification::fake();

        $this->assertSame($enrollment->user_id, $sender->id);

        $this->artisan('enrollment:transfer', [
            'enrollment' => $enrollment->id,
            'user' => $recipient->id,
            'reason' => 'Test transfer',
        ])->assertSuccessful();

        $newEnrollment = $enrollment->fresh();

        $this->assertSame($newEnrollment->user_id, $recipient->id);
        $this->assertInstanceOf(EnrollmentStates\Confirmed::class, $newEnrollment->state);

        Notification::assertCount(2);
        Notification::assertNothingSentTo($admin);
        Notification::assertSentToTimes($sender, EnrollmentTransferred::class, 1);
        Notification::assertSentToTimes($recipient, EnrollmentTransferred::class, 1);
    }
}
