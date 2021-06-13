<?php

declare(strict_types=1);

namespace Tests\Feature\Jobs;

use App\Jobs\SendActivityMessageJob;
use App\Mail\ActivityMessageMail;
use App\Models\Activity;
use App\Models\ActivityMessage;
use App\Models\States\Enrollment\Cancelled;
use App\Models\States\Enrollment\Confirmed;
use App\Models\States\Enrollment\Seeded;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;
use Tests\Traits\TestsWithEnrollments;

class TestSendActivityMessageJob extends TestCase
{
    use TestsWithEnrollments;
    use WithFaker;

    /**
     * @before
     */
    public function alwaysFakeMail(): void
    {
        $this->afterApplicationCreated(static fn () => Mail::fake());
    }

    public function test_message_to_all_message(): void
    {
        $confirmedUser = $this->getGuestUser();
        $pendingUser = $this->getGuestUser();

        $activity = factory(Activity::class)->create([
            'price' => null,
        ]);

        $this->enrollUser($activity, $confirmedUser, Confirmed::class);
        $this->enrollUser($activity, $pendingUser, Seeded::class);

        $testSubject = $this->faker->sentence;
        $testBody = $this->getMarkdownBody();

        $message = ActivityMessage::create([
            'activity_id' => $activity->id,
            'target_audience' => ActivityMessage::AUDIENCE_ANY,
            'subject' => $testSubject,
            'body' => $testBody,
        ]);

        SendActivityMessageJob::dispatchNow($message);

        Mail::assertQueued(
            ActivityMessageMail::class,
            static fn (ActivityMessageMail $mail) => (
                $mail->hasTo($pendingUser->email) &&
                $mail->getActivityMessage()->is($message)
            )
        );

        Mail::assertQueued(
            ActivityMessageMail::class,
            static fn (ActivityMessageMail $mail) => (
                $mail->hasTo($confirmedUser->email) &&
                $mail->getActivityMessage()->is($message)
            )
        );
    }

    public function test_pending_message(): void
    {
        $confirmedUser = $this->getGuestUser();
        $pendingUser = $this->getGuestUser();

        $activity = factory(Activity::class)->create([
            'price' => null,
        ]);

        $this->enrollUser($activity, $confirmedUser, Confirmed::class);
        $this->enrollUser($activity, $pendingUser, Seeded::class);

        $testSubject = $this->faker->sentence;
        $testBody = $this->getMarkdownBody();

        $message = ActivityMessage::create([
            'activity_id' => $activity->id,
            'target_audience' => ActivityMessage::AUDIENCE_PENDING,
            'subject' => $testSubject,
            'body' => $testBody,
        ]);

        SendActivityMessageJob::dispatchNow($message);

        Mail::assertQueued(
            ActivityMessageMail::class,
            static fn (ActivityMessageMail $mail) => (
                $mail->hasTo($pendingUser->email) &&
                $mail->getActivityMessage()->is($message)
            )
        );
    }

    public function test_confirmed_message(): void
    {
        $confirmedUser = $this->getGuestUser();
        $pendingUser = $this->getGuestUser();

        $activity = factory(Activity::class)->create([
            'price' => null,
        ]);

        $this->enrollUser($activity, $confirmedUser, Confirmed::class);
        $this->enrollUser($activity, $pendingUser, Seeded::class);

        $testSubject = $this->faker->sentence;
        $testBody = $this->getMarkdownBody();

        $message = ActivityMessage::create([
            'activity_id' => $activity->id,
            'target_audience' => ActivityMessage::AUDIENCE_CONFIRMED,
            'subject' => $testSubject,
            'body' => $testBody,
        ]);

        SendActivityMessageJob::dispatchNow($message);

        Mail::assertQueued(
            ActivityMessageMail::class,
            static fn (ActivityMessageMail $mail) => (
                $mail->hasTo($confirmedUser->email) &&
                $mail->getActivityMessage()->is($message)
            )
        );
    }

    public function test_cancellations_dont_get_mail(): void
    {
        $confirmedUser = $this->getGuestUser();
        $pendingUser = $this->getGuestUser();
        $cancelledUser = $this->getGuestUser();

        $activity = factory(Activity::class)->create([
            'price' => null,
        ]);

        $this->enrollUser($activity, $confirmedUser, Confirmed::class);
        $this->enrollUser($activity, $pendingUser, Seeded::class);
        $this->enrollUser($activity, $cancelledUser, Cancelled::class);

        $testSubject = $this->faker->sentence;
        $testBody = $this->getMarkdownBody();

        $message = ActivityMessage::create([
            'activity_id' => $activity->id,
            'target_audience' => ActivityMessage::AUDIENCE_ANY,
            'subject' => $testSubject,
            'body' => $testBody,
        ]);

        SendActivityMessageJob::dispatchNow($message);

        Mail::assertNotQueued(
            ActivityMessageMail::class,
            static fn (ActivityMessageMail $mail) => (
                $mail->hasTo($cancelledUser->email) &&
                $mail->getActivityMessage()->is($message)
            )
        );
    }

    /**
     * Returns random Markdown.
     */
    protected function getMarkdownBody(): string
    {
        $doc = [];

        $doc[] = <<<DOC
        # {$this->faker->sentence}

        {$this->faker->paragraphs(5, true)}
        DOC;

        for ($i = $this->faker->numberBetween(1, 5); $i > 0; $i--) {
            $doc[] = <<<DOC
            ## {$this->faker->sentence}

            {$this->faker->paragraph}
            DOC;
        }

        return implode("\n\n", $doc);
    }
}
