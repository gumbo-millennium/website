<?php

declare(strict_types=1);

namespace Tests\Feature\Jobs\Activities;

use App\Jobs\Activities\CheckActivityForFeatureMails;
use App\Models\Activity;
use App\Models\Enrollment;
use App\Notifications\Activities\ActivityFeatureNotification;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class CheckActivityFeatureMailsTest extends TestCase
{
    public function test_activity_with_no_features(): void
    {
        Notification::fake();

        $activity = factory(Activity::class)->create([
            'start_date' => Date::parse('2021-06-01 21:00:00'),
        ]);

        CheckActivityForFeatureMails::dispatchNow($activity);

        Notification::assertNothingSent();
    }

    public function test_activity_with_feature_without_mail(): void
    {
        Notification::fake();

        Config::set('gumbo.activity-features.test-123', [
            'title' => 'I am example',
            'icon' => 'steve',
        ]);

        $activity = factory(Activity::class)->create([
            'start_date' => Date::parse('2021-06-01 21:00:00'),
            'features' => [
                'test-123' => true,
            ],
        ]);

        CheckActivityForFeatureMails::dispatchNow($activity);

        Notification::assertNothingSent();
    }

    public function test_activity_with_invalid_feature(): void
    {
        Notification::fake();

        Config::set('gumbo.activity-features.test-not-found', null);

        $activity = factory(Activity::class)->create([
            'start_date' => Date::parse('2021-06-01 21:00:00'),
            'features' => [
                'test-not-found' => true,
            ],
        ]);

        CheckActivityForFeatureMails::dispatchNow($activity);

        Notification::assertNothingSent();
    }

    public function test_activity_with_basic_mail()
    {
        Config::set('gumbo.activity-features.test-simple', [
            'title' => 'Test Simple',
            'icon' => 'wrench',
            'mail' => [
                'send' => 'PT6H',
                'subject' => 'Test mail',
                'body' => <<<'DOC'
                Hello World,

                This is a test for a mail to {user}.
                DOC,
            ],
        ]);

        Notification::fake();

        $activity = factory(Activity::class)->create([
            'start_date' => Date::parse('2021-06-01 21:00:00'),
            'features' => [
                'test-simple' => true,
            ],
        ]);

        $user = $this->getMemberUser();

        Enrollment::unguarded(fn () => $activity->enrollments()->create([
            'user_id' => $user->id,
        ]));

        // Check if before the window, nothing is sent
        Date::setTestNow('2021-06-01 12:00:00');
        CheckActivityForFeatureMails::dispatchNow($activity);
        Notification::assertNothingSent();

        // Check if after start nothing is sent
        Date::setTestNow('2021-06-01 23:00:00');
        CheckActivityForFeatureMails::dispatchNow($activity);
        Notification::assertNothingSent();

        // Check that, when the window is reached, the mail is sent
        Date::setTestNow('2021-06-01 16:00:00');
        CheckActivityForFeatureMails::dispatchNow($activity);
        Notification::assertSentToTimes($user, ActivityFeatureNotification::class, 1);

        // Check that running the job again won't re-send mails
        Notification::fake();
        CheckActivityForFeatureMails::dispatchNow($activity);
        Notification::assertNothingSent();
    }
}
