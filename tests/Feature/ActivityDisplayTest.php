<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Activity;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ActivityDisplayTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Ensures there are some activities
     * @return void
     */
    public function seedBefore(): void
    {
        $this->seed('ActivitySeeder');
    }

    /**
     * Get test route
     * @param string $route
     * @param null|User $user
     * @param null|Activity $activity
     * @return void
     * @dataProvider provideTestRoutes
     */
    public function testVariousRoutes(string $route, ?User $user, ?Activity $activity): void
    {
        // Run proper command
        $response = $user ? ($this->actingAs($user)->get($route)) : $this->get($route);

        // Run user-level command
        $response->assertStatus(200);

        // Check if a title should be set
        if ($activity) {
            $response->assertSeeText($activity->title);
        }
    }

    /**
     * Provides a list of test routes
     * @return array<array<string>>
     */
    public function provideTestRoutes()
    {
        // Ensure theres an app exists
        $this->ensureApplicationExists();

        // Get users
        $guest = $this->getGuestUser();
        $member = $this->getMemberUser();

        // get activity
        $getFirstActivity = static fn ($user) => Activity::getNextActivities($user)->first();

        // Get routes
        $activityAnon = $getFirstActivity(null);
        $activityGuest = $getFirstActivity($guest);
        $activityMember = $getFirstActivity($member);

        return [
            // Index page
            'Index (anonymous)' => [route('activity.index'), null, $activityAnon],
            'Index (guest)' => [route('activity.index'), $guest, $activityGuest],
            'Index (member)' => [route('activity.index'), $member, $activityMember],

            // Details page
            'Detail (anonymous)' => [route('activity.show', ['activity' => $activityAnon]), null, null],
            'Detail (guest)' => [route('activity.show', ['activity' => $activityGuest]), $guest, null],
            'Detail (member)' => [route('activity.show', ['activity' => $activityMember]), $member, null],
        ];
    }
}
