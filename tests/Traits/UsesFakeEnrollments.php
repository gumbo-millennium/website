<?php

declare(strict_types=1);

namespace Tests\Traits;

use App\Contracts\EnrollmentServiceContract;
use App\Models\Activity;
use App\Models\Enrollment;

trait UsesFakeEnrollments
{
    private ?Activity $paidActivity = null;
    private ?Activity $discountActivity = null;
    private ?Activity $memberFreeActivity = null;
    private ?Activity $freeActivity = null;

    /**
     * Returns an enrollment that needs to be paid
     * @return Enrollment
     * @throws BindingResolutionException
     */
    private function createPaidEnrollment(): Enrollment
    {
        // Get some props
        $this->paidActivity ??= \factory(Activity::class)->states('paid')->create()->first();
        $user = $this->getMemberUser();

        // Get enrollment
        $service = app(EnrollmentServiceContract::class);
        \assert($service instanceof EnrollmentServiceContract);

        // Make enrollment
        $enrollment = $service->createEnrollment($this->paidActivity, $user);
        $service->advanceEnrollment($this->paidActivity, $enrollment);

        // Return
        return $enrollment;
    }
    /**
     * Returns an enrollment that has a discount but needs to be paid
     * @return Enrollment
     * @throws BindingResolutionException
     */
    private function createDiscountEnrollment(): Enrollment
    {
        // Get some props
        $this->discountActivity ??= \factory(Activity::class)->states('paid', 'member-discount')->create()->first();
        $user = $this->getMemberUser();

        // Get enrollment
        $service = app(EnrollmentServiceContract::class);
        \assert($service instanceof EnrollmentServiceContract);

        // Make enrollment
        $enrollment = $service->createEnrollment($this->discountActivity, $user);
        $service->advanceEnrollment($this->discountActivity, $enrollment);

        // Return
        return $enrollment;
    }
    /**
     * Returns an enrollment that has a discount but needs to be paid
     * @return Enrollment
     * @throws BindingResolutionException
     */
    private function createMemberFreeEnrollment(): Enrollment
    {
        // Get some props
        $this->memberFreeActivity ??= \factory(Activity::class)->states('paid', 'member-free')->create()->first();
        $user = $this->getMemberUser();

        // Get enrollment
        $service = app(EnrollmentServiceContract::class);
        \assert($service instanceof EnrollmentServiceContract);

        // Make enrollment
        $enrollment = $service->createEnrollment($this->memberFreeActivity, $user);
        $service->advanceEnrollment($this->memberFreeActivity, $enrollment);

        // Return
        return $enrollment;
    }
    /**
     * Returns an enrollment that has a discount but needs to be paid
     * @return Enrollment
     * @throws BindingResolutionException
     */
    private function createFreeEnrollment(): Enrollment
    {
        // Get some props
        $this->freeActivity ??= \factory(Activity::class)->create()->first();
        $user = $this->getMemberUser();

        // Get enrollment
        $service = app(EnrollmentServiceContract::class);
        \assert($service instanceof EnrollmentServiceContract);

        // Make enrollment
        $enrollment = $service->createEnrollment($this->freeActivity, $user);
        $service->advanceEnrollment($this->freeActivity, $enrollment);

        // Return
        return $enrollment;
    }
}
