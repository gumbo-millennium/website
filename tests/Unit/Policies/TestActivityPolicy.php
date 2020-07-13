<?php

declare(strict_types=1);

namespace Tests\Unit\Policies;

use App\Models\Activity;
use App\Models\Role;
use App\Models\States\Enrollment\Confirmed;
use App\Models\States\Enrollment\Paid;
use App\Policies\ActivityPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\CreatesDummyActivityModels;
use Tests\Traits\MutatesActivities;

class TestActivityPolicy extends TestCase
{
    use RefreshDatabase;
    use CreatesDummyActivityModels;
    use MutatesActivities;

    /**
     * Test against a guest user
     */
    public function testGuestPermissions(): void
    {
        // Get user
        $user = $this->getGuestUser();

        // Test generic
        $this->assertFalse($user->can('viewAny', Activity::class));
        $this->assertFalse($user->can('create', Activity::class));

        // Get activities
        $publicActivity = $this->createDummyActivity(null, true);
        $privateActivity = $this->createDummyActivity(null, false);

        // Assume they can see and enroll onto public events
        $this->assertTrue($user->can('view', $publicActivity));
        $this->assertTrue($user->can('enroll', $publicActivity));

        // Assume other management is disallowed
        $this->assertFalse($user->can('update', $publicActivity));
        $this->assertFalse($user->can('cancel', $publicActivity));
        $this->assertFalse($user->can('delete', $publicActivity));
        $this->assertFalse($user->can('restore', $publicActivity));
        $this->assertFalse($user->can('forceDelete', $publicActivity));

        // Assume viewing and enrolling on the private activity
        // is not allowed
        $this->assertFalse($user->can('view', $privateActivity));
        $this->assertFalse($user->can('enroll', $privateActivity));
    }

    /**
     * Test against a guest user
     */
    public function testMemberPermissions(): void
    {
        // Get user
        $user = $this->getMemberUser();

        // Test generic
        $this->assertFalse($user->can('viewAny', Activity::class));
        $this->assertFalse($user->can('create', Activity::class));

        // Get activities
        $publicActivity = $this->createDummyActivity(null, true);
        $privateActivity = $this->createDummyActivity(null, false);

        // Assume they can see and enroll onto public events
        $this->assertTrue($user->can('view', $publicActivity));
        $this->assertTrue($user->can('enroll', $publicActivity));

        // Assume other management is disallowed
        $this->assertFalse($user->can('update', $publicActivity));
        $this->assertFalse($user->can('cancel', $publicActivity));
        $this->assertFalse($user->can('delete', $publicActivity));
        $this->assertFalse($user->can('restore', $publicActivity));
        $this->assertFalse($user->can('forceDelete', $publicActivity));

        // Assume viewing and enrolling on the private activity
        // is allowed for this member
        $this->assertTrue($user->can('view', $privateActivity));
        $this->assertTrue($user->can('enroll', $privateActivity));
    }

    /**
     * Test against a member of the AC (who manage events)
     */
    public function testCommissionPermissions(): void
    {
        // Get user
        $acUser = $this->getCommissionUser('ac');
        $lhwUser = $this->getCommissionUser('lhw');

        // Test generic
        $this->assertTrue($acUser->can('viewAny', Activity::class));
        $this->assertTrue($acUser->can('create', Activity::class));

        // Test generic
        $this->assertFalse($lhwUser->can('viewAny', Activity::class));
        $this->assertFalse($lhwUser->can('create', Activity::class));

        // Get activities
        $acOwnedActivity = $this->createDummyActivity(Role::findByName('ac'));
        $lhwOwnedActivity = $this->createDummyActivity(Role::findByName('lhw'));
        $notOwnedActivity = $this->createDummyActivity(null);

        // IB now has an activity, so should be able to view but not create
        $this->assertTrue($lhwUser->can('viewAny', Activity::class));
        $this->assertFalse($lhwUser->can('create', Activity::class));

        // Assume all events can be managed by AC
        $this->assertTrue($acUser->can('manage', $acOwnedActivity));
        $this->assertTrue($acUser->can('manage', $lhwOwnedActivity));
        $this->assertTrue($acUser->can('manage', $notOwnedActivity));

        // Assume own events can be managed by IB
        $this->assertTrue($lhwUser->can('manage', $lhwOwnedActivity));
        $this->assertFalse($lhwUser->can('manage', $acOwnedActivity));
        $this->assertFalse($lhwUser->can('manage', $notOwnedActivity));

        // Quick check enrollments
        $this->assertTrue($lhwUser->can('addEnrollment', $lhwOwnedActivity));
        $this->assertFalse($lhwUser->can('addEnrollment', $acOwnedActivity));
    }

    /**
     * Test purging
     */
    public function testPurgingPermissions(): void
    {
        // Get normal user
        $acUser = $this->getCommissionUser('ac');

        // Get admin user
        $acAdminUser = $this->getCommissionUser('ac');
        $acAdminUser->givePermissionTo(ActivityPolicy::PURGE_PERMISSION);

        // Get activities
        $acActivity = $this->createDummyActivity(Role::findByName('ac'));
        $notOwnedActivity = $this->createDummyActivity(null);

        // Ensure the user cannot purge
        $this->assertFalse($acUser->hasPermissionTo(ActivityPolicy::PURGE_PERMISSION));
        $this->assertTrue($acAdminUser->hasPermissionTo(ActivityPolicy::PURGE_PERMISSION));

        // Ensure the activity cannot be pruned by AC
        $this->assertFalse($acUser->can('forceDelete', $acActivity));
        $this->assertFalse($acUser->can('forceDelete', $notOwnedActivity));

        // Ensure the activities can be pruned by board
        $this->assertTrue($acAdminUser->can('forceDelete', $acActivity));
        $this->assertTrue($acAdminUser->can('forceDelete', $notOwnedActivity));
    }

    /**
     * Tests if future and past constraints work
     */
    public function testReadOnlyWhenPast()
    {
        // Get user
        $user = $this->getCommissionUser('ac');

        // Get activities
        $futureActivity = $this->createDummyActivity(null, true, \now()->addWeek());
        $pastActivity = $this->createDummyActivity(null, true, \now()->subWeek());

        // Assume events from ourselves can be managed
        $this->assertTrue($user->can('update', $futureActivity));
        $this->assertTrue($user->can('cancel', $futureActivity));

        // Assume past events are read-only
        $this->assertFalse($user->can('update', $pastActivity));
        $this->assertFalse($user->can('cancel', $pastActivity));

        // Assume both types can be deleted
        $this->assertTrue($user->can('delete', $futureActivity));
        $this->assertTrue($user->can('delete', $pastActivity));

        // Assume only future activities can be restored
        $this->assertTrue($user->can('restore', $futureActivity));
        $this->assertFalse($user->can('restore', $pastActivity));
    }

    public function testPaymentProtections()
    {
        // Get user
        $user = $this->getCommissionUser('ac');

        // Get activity
        $activityWithPaid = $this->createDummyActivity(null, true);

        // Create paid enrollment
        $enrollment = $this->createDummyEnrollment($user, $activityWithPaid);
        $enrollment->transitionTo(Paid::class);
        $enrollment->save();

        // Check
        $this->assertFalse($user->can('delete', $activityWithPaid));

        // Get activity
        $activityWithoutPaid = $this->createDummyActivity(null, true);

        // Create enrollment
        $enrollment = $this->createDummyEnrollment($user, $activityWithoutPaid);
        $enrollment->transitionTo(Confirmed::class);
        $enrollment->save();

        // Check
        $this->assertTrue($user->can('delete', $activityWithoutPaid));
    }

    public function testNewActivityDeletions()
    {
        // Get user
        $user = $this->getCommissionUser('ac');

        // Get activity
        $freshActivity = $this->createDummyActivity(null, true);
        $oldActivity = $this->createDummyActivity(null, true);

        // Update
        $oldActivity->created_at = \now()->subDays(3)->subHour();
        $oldActivity->save();

        // Check
        $this->assertTrue($user->can('delete', $freshActivity));
        $this->assertFalse($user->can('delete', $oldActivity));
    }

    /**
     * Tests if deleting old activities works
     */
    public function testDeleteOldActivities()
    {
        // Get user
        $user = $this->getCommissionUser('ac');

        // Get activities
        $activeOneYear = $this->createDummyActivity(null, true, \now()->subYear());
        $activeOneMonth = $this->createDummyActivity(null, true, \now()->subMonth());
        $activeOneWeek = $this->createDummyActivity(null, true, \now()->subWeek());
        $cancelledOneYear = $this->createDummyActivity(null, true, \now()->subYear());
        $cancelledOneMonth = $this->createDummyActivity(null, true, \now()->subYear());
        $cancelledOneWeek = $this->createDummyActivity(null, true, \now()->subWeek());

        // Cancel the cancelled activities
        $this->cancelActivity($cancelledOneYear);
        $this->cancelActivity($cancelledOneMonth);
        $this->cancelActivity($cancelledOneWeek);

        foreach (
            [
            $activeOneYear,
            $activeOneMonth,
            $activeOneWeek,
            $cancelledOneYear,
            $cancelledOneMonth,
            $cancelledOneWeek
            ] as $activity
        ) {
            $activity->created_at = now()->subCentury();
        }

        // Check actives
        $this->assertTrue($user->can('delete', $activeOneYear));
        $this->assertFalse($user->can('delete', $activeOneMonth));
        $this->assertFalse($user->can('delete', $activeOneWeek));

        // Check cancelled
        $this->assertTrue($user->can('delete', $cancelledOneYear));
        $this->assertTrue($user->can('delete', $cancelledOneMonth));
        $this->assertFalse($user->can('delete', $cancelledOneWeek));
    }

    public function testEnrollConditions()
    {
        // Get user
        $guestUser = $this->getGuestUser();
        $memberUser = $this->getMemberUser();
        $acUser = $this->getCommissionUser('ac');
        $boardUser = $this->getBoardUser();

        // Get basic activities
        $publicActivity = $this->createDummyActivity(null, true);
        $privateActivity = $this->createDummyActivity(null, false);

        // Get cancelled activities
        $publicCancelledActivity = $this->cancelActivity($this->createDummyActivity(null, true));
        $privateCancelledActivity = $this->cancelActivity($this->createDummyActivity(null, false));

        // Create full activities
        $publicFullActivity = $this->createDummyActivity(null, true);
        $privateFullActivity = $this->createDummyActivity(null, false);

        // Sell out the required activities
        $this->sellOutActivity($publicFullActivity);
        $this->sellOutActivity($privateFullActivity);

        // The guest should only be allowed once
        $this->assertTrue($guestUser->can('enroll', $publicActivity));
        $this->assertFalse($guestUser->can('enroll', $privateActivity));
        $this->assertFalse($guestUser->can('enroll', $publicCancelledActivity));
        $this->assertFalse($guestUser->can('enroll', $privateCancelledActivity));
        $this->assertFalse($guestUser->can('enroll', $publicFullActivity));
        $this->assertFalse($guestUser->can('enroll', $privateFullActivity));

        // The member, ac and board should be allowed twice
        foreach ([$memberUser, $acUser, $boardUser] as $user) {
            $this->assertTrue($user->can('enroll', $publicActivity));
            $this->assertTrue($user->can('enroll', $privateActivity));
            $this->assertFalse($user->can('enroll', $publicCancelledActivity));
            $this->assertFalse($user->can('enroll', $privateCancelledActivity));
            $this->assertFalse($user->can('enroll', $publicFullActivity));
            $this->assertFalse($user->can('enroll', $privateFullActivity));
        }
    }
}
