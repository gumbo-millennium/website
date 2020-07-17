<?php

declare(strict_types=1);

namespace Tests\Unit\Policies;

use App\Models\Enrollment;
use App\Models\States\Enrollment\Cancelled;
use App\Models\States\Enrollment\Confirmed;
use App\Models\States\Enrollment\Paid;
use App\Models\States\Enrollment\Seeded;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\CreatesDummyActivityModels;

class TestEnrollmentPolicy extends TestCase
{
    use RefreshDatabase;
    use CreatesDummyActivityModels;

    /**
     * Test against a guest user
     */
    public function testManagementPermissions(): void
    {
        // Get user
        $guestUser = $this->getGuestUser();
        $memberUser = $this->getMemberUser();
        $acUser = $this->getCommissionUser('ac');
        $lhwUser = $this->getCommissionUser('lhw');
        $pcUser = $this->getCommissionUser('pc');

        // Make an activity for the LHW user
        $lhwActivity = $this->createDummyActivity('lhw');
        $randomActivity = $this->createDummyActivity();

        // Get LHW and random enrollment
        $lhwEnrollment = $this->createDummyEnrollment($guestUser, $lhwActivity);
        $randomEnrollment = $this->createDummyEnrollment($guestUser, $randomActivity);

        // Assume user, member and IC cannot see
        $this->assertFalse($guestUser->can('viewAny', Enrollment::class));
        $this->assertFalse($guestUser->can('create', Enrollment::class));
        $this->assertFalse($guestUser->can('view', $randomEnrollment));
        $this->assertFalse($guestUser->can('update', $randomEnrollment));
        $this->assertFalse($guestUser->can('refund', $randomEnrollment));

        $this->assertFalse($memberUser->can('viewAny', Enrollment::class));
        $this->assertFalse($memberUser->can('create', Enrollment::class));
        $this->assertFalse($memberUser->can('view', $randomEnrollment));
        $this->assertFalse($memberUser->can('update', $randomEnrollment));
        $this->assertFalse($memberUser->can('refund', $randomEnrollment));

        $this->assertFalse($pcUser->can('viewAny', Enrollment::class));
        $this->assertFalse($pcUser->can('create', Enrollment::class));
        $this->assertFalse($pcUser->can('view', $randomEnrollment));
        $this->assertFalse($pcUser->can('update', $randomEnrollment));
        $this->assertFalse($pcUser->can('refund', $randomEnrollment));

        // Assume AC and LHW can manage
        $this->assertTrue($acUser->can('viewAny', Enrollment::class));
        $this->assertTrue($acUser->can('create', Enrollment::class));
        $this->assertTrue($lhwUser->can('viewAny', Enrollment::class));
        $this->assertTrue($lhwUser->can('create', Enrollment::class));

        // Assume AC can edit both enrollments
        $this->assertTrue($acUser->can('update', $lhwEnrollment));
        $this->assertTrue($acUser->can('update', $randomEnrollment));

        // Assume LHW can edit LHW enrollments
        $this->assertTrue($lhwUser->can('update', $lhwEnrollment));
        $this->assertFalse($lhwUser->can('update', $randomEnrollment));

        // Test manage for guest
        $this->assertFalse($guestUser->can('manage', $lhwEnrollment));
        $this->assertFalse($guestUser->can('manage', $randomEnrollment));

        // Test manage for AC
        $this->assertTrue($acUser->can('manage', $lhwEnrollment));
        $this->assertTrue($acUser->can('manage', $randomEnrollment));

        // Test manage for LHW
        $this->assertTrue($lhwUser->can('manage', $lhwEnrollment));
        $this->assertFalse($lhwUser->can('manage', $randomEnrollment));
    }

    public function testDeletionDisallowed()
    {
        // Get user
        $guestUser = $this->getGuestUser();
        $memberUser = $this->getMemberUser();
        $acUser = $this->getCommissionUser('ac');
        $lhwUser = $this->getCommissionUser('lhw');
        $boardUser = $this->getBoardUser();

        // Get random enrollment
        $enrollment = $this->createDummyEnrollment($this->getMemberUser());

        // Nobody should be allowed to pass
        $this->assertFalse($guestUser->can('delete', $enrollment));
        $this->assertFalse($memberUser->can('delete', $enrollment));
        $this->assertFalse($acUser->can('delete', $enrollment));
        $this->assertFalse($lhwUser->can('delete', $enrollment));
        $this->assertFalse($boardUser->can('delete', $enrollment));
    }

    /**
     * Test against a guest user
     */
    public function testUnenrollOwnership(): void
    {
        // Get user
        $guestUser = $this->getGuestUser();
        $acUser = $this->getCommissionUser('ac');
        $lhwUser = $this->getCommissionUser('lhw');

        // Get two activities

        // Make an activity for the LHW user
        $lhwActivity = $this->createDummyActivity('lhw');
        $randomActivity = $this->createDummyActivity();

        // Get LHW and random enrollment
        $lhwEnrollment = $this->createDummyEnrollment($guestUser, $lhwActivity);
        $randomEnrollment = $this->createDummyEnrollment($guestUser, $randomActivity);
        $foreignEnrollment = $this->createDummyEnrollment($this->getGuestUser(), $randomActivity);

        // Test lhw enrollment
        $this->assertTrue($guestUser->can('unenroll', $lhwEnrollment));
        $this->assertFalse($acUser->can('unenroll', $lhwEnrollment));
        $this->assertFalse($lhwUser->can('unenroll', $lhwEnrollment));

        // Test random enrollment
        $this->assertTrue($guestUser->can('unenroll', $randomEnrollment));
        $this->assertFalse($acUser->can('unenroll', $randomEnrollment));
        $this->assertFalse($lhwUser->can('unenroll', $randomEnrollment));

        // Test random, foreign enrollment
        $this->assertFalse($acUser->can('unenroll', $foreignEnrollment));
        $this->assertFalse($guestUser->can('unenroll', $foreignEnrollment));
        $this->assertFalse($lhwUser->can('unenroll', $foreignEnrollment));
    }

    /**
     * Test against a member of the AC (who manage events)
     */
    public function testUnenrollmentWithStates(): void
    {
        // Get user
        $user = $this->getGuestUser();
        $enrollment = $this->createDummyEnrollment($user);

        // Test regular
        $this->assertTrue($user->can('unenroll', $enrollment));

        // Mark the enrollment as paid
        $enrollment->transitionTo(Seeded::class);
        $enrollment->save();

        // Test if paid is impossible to unenroll
        $this->assertTrue($user->can('unenroll', $enrollment));

        // Mark the enrollment as paid
        $enrollment->transitionTo(Confirmed::class);
        $enrollment->save();

        // Test if paid is impossible to unenroll
        $this->assertTrue($user->can('unenroll', $enrollment));

        // Mark the enrollment as paid
        $enrollment->transitionTo(Paid::class);
        $enrollment->save();

        // Test if paid is impossible to unenroll
        $this->assertFalse($user->can('unenroll', $enrollment));

        // Mark the enrollment as paid
        $enrollment->transitionTo(Cancelled::class);
        $enrollment->save();

        // Test if paid is impossible to unenroll
        $this->assertFalse($user->can('unenroll', $enrollment));
    }

    /**
     * Test against a member of the AC (who manage events)
     */
    public function testUnenrollmentWithActivityLocks(): void
    {
        // Get user
        $user = $this->getGuestUser();
        $enrollment = $this->createDummyEnrollment($user);
        $activity = $enrollment->activity;

        // Test regular
        $this->assertTrue($user->can('unenroll', $enrollment));

        // Close enrollments
        $activity->enrollment_end = \now()->subDay();
        $activity->save();

        // Should still allow as unstable
        $this->assertTrue($user->can('unenroll', $enrollment));

        // Mark the enrollment as expiring, in case of last-minute sign-up
        $enrollment->transitionTo(Seeded::class);
        $enrollment->expire = now()->addWeek();
        $enrollment->save();

        // Seeded is not stable, and it's not expired, so allow
        $this->assertTrue($user->can('unenroll', $enrollment));

        // Mark the enrollment as paid
        $enrollment->transitionTo(Confirmed::class);
        $enrollment->save();

        // Paid is stable, so should block
        $this->assertFalse($user->can('unenroll', $enrollment));
    }

    public function testAdminUnenroll()
    {
        // Get users
        $guestUser = $this->getGuestUser();
        $lhwUser = $this->getCommissionUser('lhw');
        $acUser = $this->getCommissionUser('ac');

        // Get activity
        $lhwActivity = $this->createDummyActivity('lhw');

        // Get the enrollments
        $lhwEnrollment = $this->createDummyEnrollment($guestUser, $lhwActivity);
        $randomEnrollment = $this->createDummyEnrollment($guestUser);

        // Test regular
        $this->assertFalse($guestUser->can('adminUnenroll', $lhwEnrollment));
        $this->assertTrue($lhwUser->can('adminUnenroll', $lhwEnrollment));
        $this->assertTrue($acUser->can('adminUnenroll', $lhwEnrollment));
        $this->assertFalse($guestUser->can('adminUnenroll', $randomEnrollment));
        $this->assertFalse($lhwUser->can('adminUnenroll', $randomEnrollment));
        $this->assertTrue($acUser->can('adminUnenroll', $randomEnrollment));

        // Mark the enrollments as cancelled
        $lhwEnrollment->transitionTo(Cancelled::class);
        $lhwEnrollment->save();
        $randomEnrollment->transitionTo(Cancelled::class);
        $randomEnrollment->save();

        // Test paid, should lock
        $this->assertFalse($guestUser->can('adminUnenroll', $lhwEnrollment));
        $this->assertFalse($lhwUser->can('adminUnenroll', $lhwEnrollment));
        $this->assertFalse($acUser->can('adminUnenroll', $lhwEnrollment));
        $this->assertFalse($guestUser->can('adminUnenroll', $randomEnrollment));
        $this->assertFalse($lhwUser->can('adminUnenroll', $randomEnrollment));
        $this->assertFalse($acUser->can('adminUnenroll', $randomEnrollment));
    }
}
