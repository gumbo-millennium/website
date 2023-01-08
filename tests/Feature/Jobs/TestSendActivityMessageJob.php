<?php

declare(strict_types=1);

namespace Tests\Feature\Jobs;

use App\Facades\Enroll;
use App\Jobs\SendActivityMessageJob;
use App\Mail\ActivityMessageMail;
use App\Models\Activity;
use App\Models\ActivityMessage;
use App\Models\States\Enrollment\Cancelled;
use App\Models\States\Enrollment\Confirmed;
use App\Models\States\Enrollment\Created;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class TestSendActivityMessageJob extends TestCase
{
    use WithFaker;

    /**
     * @before
     */
    public function ensureSystemsRunMinimal(): void
    {
        $this->afterApplicationCreated(function () {
            Mail::fake();
            Config::set('services.google.wallet.enabled', false);
        });
    }

    public function test_default_behaviour(): void
    {
        /** @var User */
        [$randomUser, $pendingUser, $confirmedUser] = User::factory()->createMany([
            ['first_name' => 'Random', 'last_name' => 'User'],
            ['first_name' => 'Pending', 'last_name' => 'User'],
            ['first_name' => 'Confirmed', 'last_name' => 'User'],
        ]);

        $activity = Activity::factory()->create();
        $ticket = Ticket::factory()->for($activity)->create();

        $this->actingAs($pendingUser);
        $pendingEnrollment = Enroll::createEnrollment($activity, $ticket);

        $this->actingAs($confirmedUser);
        $confirmedEnrollment = Enroll::createEnrollment($activity, $ticket);
        $confirmedEnrollment->state->transitionTo(Confirmed::class);

        $this->assertInstanceOf(Created::class, $pendingEnrollment->state);
        $this->assertInstanceOf(Confirmed::class, $confirmedEnrollment->state);

        // Send the message and run the mail job
        $message = ActivityMessage::create([
            'activity_id' => $activity->id,
            'subject' => $this->faker->sentence,
            'body' => $this->faker->sentences(6, true),
        ]);
        SendActivityMessageJob::dispatch($message);

        // Test normal message is not queued for pending user
        Mail::assertNotQueued(ActivityMessageMail::class, fn (ActivityMessageMail $mail) => $mail->hasTo($randomUser->email));
        Mail::assertNotQueued(ActivityMessageMail::class, fn (ActivityMessageMail $mail) => $mail->hasTo($pendingUser->email));
        Mail::assertQueued(ActivityMessageMail::class, fn (ActivityMessageMail $mail) => $mail->hasTo($confirmedUser->email));
    }

    public function test_includ_pending_behaviour(): void
    {
        /** @var User */
        [$randomUser, $pendingUser, $confirmedUser] = User::factory()->createMany([
            ['first_name' => 'Random', 'last_name' => 'User'],
            ['first_name' => 'Pending', 'last_name' => 'User'],
            ['first_name' => 'Confirmed', 'last_name' => 'User'],
        ]);

        $activity = Activity::factory()->create();
        $ticket = Ticket::factory()->for($activity)->create();

        $this->actingAs($pendingUser);
        $pendingEnrollment = Enroll::createEnrollment($activity, $ticket);

        $this->actingAs($confirmedUser);
        $confirmedEnrollment = Enroll::createEnrollment($activity, $ticket);
        $confirmedEnrollment->state->transitionTo(Confirmed::class);

        $this->assertInstanceOf(Created::class, $pendingEnrollment->state);
        $this->assertInstanceOf(Confirmed::class, $confirmedEnrollment->state);

        // Send the message and run the mail job
        $message = ActivityMessage::create([
            'activity_id' => $activity->id,
            'include_pending' => true,
            'subject' => $this->faker->sentence,
            'body' => $this->faker->sentences(6, true),
        ]);
        SendActivityMessageJob::dispatch($message);

        // Test normal message is not queued for pending user
        Mail::assertNotQueued(ActivityMessageMail::class, fn (ActivityMessageMail $mail) => $mail->hasTo($randomUser->email));
        Mail::assertQueued(ActivityMessageMail::class, fn (ActivityMessageMail $mail) => $mail->hasTo($pendingUser->email));
        Mail::assertQueued(ActivityMessageMail::class, fn (ActivityMessageMail $mail) => $mail->hasTo($confirmedUser->email));
    }

    public function test_ticket_targetting(): void
    {
        /** @var User */
        [$randomUser, $pendingTicketOne, $pendingTicketTwo, $confirmedTicketOne, $confirmedTicketTwo] = User::factory()->createMany([
            ['first_name' => 'Random', 'last_name' => 'User'],
            ['first_name' => 'Pending', 'last_name' => 'Ticket One'],
            ['first_name' => 'Pending', 'last_name' => 'Ticket Two'],
            ['first_name' => 'Confirmed', 'last_name' => 'Ticket One'],
            ['first_name' => 'Confirmed', 'last_name' => 'Ticket Two'],
        ]);

        $activity = Activity::factory()->create();
        [$ticketOne, $ticketTwo] = Ticket::factory()->times(2)->for($activity)->create();

        $this->actingAs($pendingTicketOne);
        $pendingEnrollmentT1 = Enroll::createEnrollment($activity, $ticketOne);

        $this->actingAs($pendingTicketTwo);
        $pendingEnrollmentT2 = Enroll::createEnrollment($activity, $ticketTwo);

        $this->actingAs($confirmedTicketOne);
        $confirmedEnrollmentT1 = Enroll::createEnrollment($activity, $ticketOne);
        $confirmedEnrollmentT1->state->transitionTo(Confirmed::class);

        $this->actingAs($confirmedTicketTwo);
        $confirmedEnrollmentT2 = Enroll::createEnrollment($activity, $ticketTwo);
        $confirmedEnrollmentT2->state->transitionTo(Confirmed::class);

        $this->assertInstanceOf(Created::class, $pendingEnrollmentT1->state);
        $this->assertInstanceOf(Created::class, $pendingEnrollmentT2->state);
        $this->assertInstanceOf(Confirmed::class, $confirmedEnrollmentT1->state);
        $this->assertInstanceOf(Confirmed::class, $confirmedEnrollmentT2->state);

        $this->assertTrue($pendingEnrollmentT1->ticket->is($ticketOne));
        $this->assertTrue($pendingEnrollmentT2->ticket->is($ticketTwo));
        $this->assertTrue($confirmedEnrollmentT1->ticket->is($ticketOne));
        $this->assertTrue($confirmedEnrollmentT2->ticket->is($ticketTwo));

        // Send the message and run the mail job
        $message = ActivityMessage::create([
            'activity_id' => $activity->id,
            'subject' => $this->faker->sentence,
            'body' => $this->faker->sentences(6, true),
        ]);

        $message->tickets()->sync([$ticketTwo->id]);
        $message->save();

        SendActivityMessageJob::dispatch($message);

        // Test normal message is not queued for pending user
        Mail::assertNotQueued(ActivityMessageMail::class, fn (ActivityMessageMail $mail) => $mail->hasTo($randomUser->email));
        Mail::assertNotQueued(ActivityMessageMail::class, fn (ActivityMessageMail $mail) => $mail->hasTo($pendingTicketOne->email));
        Mail::assertNotQueued(ActivityMessageMail::class, fn (ActivityMessageMail $mail) => $mail->hasTo($pendingTicketTwo->email));
        Mail::assertNotQueued(ActivityMessageMail::class, fn (ActivityMessageMail $mail) => $mail->hasTo($confirmedTicketOne->email));

        Mail::assertQueued(ActivityMessageMail::class, fn (ActivityMessageMail $mail) => $mail->hasTo($confirmedTicketTwo->email));
    }

    public function test_cancellations_dont_get_mail(): void
    {
        /** @var User */
        [$confirmed, $cancelled] = User::factory()->createMany([
            ['first_name' => 'Confirmed', 'last_name' => 'User'],
            ['first_name' => 'Cancelled', 'last_name' => 'User'],
        ]);

        $activity = Activity::factory()->create();
        $ticket = Ticket::factory()->for($activity)->create();

        $this->actingAs($confirmed);
        $confirmedEnrollment = Enroll::createEnrollment($activity, $ticket);
        $confirmedEnrollment->state->transitionTo(Confirmed::class);

        $this->actingAs($cancelled);
        $cancelledEnrollment = Enroll::createEnrollment($activity, $ticket);
        $cancelledEnrollment->state->transitionTo(Cancelled::class);

        $this->assertInstanceOf(Confirmed::class, $confirmedEnrollment->state);
        $this->assertInstanceOf(Cancelled::class, $cancelledEnrollment->state);

        // Send the message and run the mail job
        $message = ActivityMessage::create([
            'activity_id' => $activity->id,
            'subject' => $this->faker->sentence,
            'body' => $this->faker->sentences(6, true),
        ]);
        SendActivityMessageJob::dispatch($message);

        // Test normal message is not queued for pending user
        Mail::assertNotQueued(ActivityMessageMail::class, fn (ActivityMessageMail $mail) => $mail->hasTo($cancelled->email));
        Mail::assertQueued(ActivityMessageMail::class, fn (ActivityMessageMail $mail) => $mail->hasTo($confirmed->email));
    }
}
