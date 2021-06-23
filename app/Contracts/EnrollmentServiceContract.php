<?php

declare(strict_types=1);

namespace App\Contracts;

use App\Models\Activity;
use App\Models\Enrollment;
use App\Models\User;
use Illuminate\Contracts\Cache\Lock;
use LogicException;

interface EnrollmentServiceContract
{
    /**
     * Returns true if the service uses locks and apps should support it.
     */
    public function useLocks(): bool;

    /**
     * Returns a lock to enroll the given user.
     *
     * @throws LogicException if locsk are not supported
     */
    public function getLock(Activity $activity): Lock;

    /**
     * Returns true if the activity allows new enrollments and the user is allowed to enroll (if given).
     */
    public function canEnroll(Activity $activity, ?User $user): bool;

    /**
     * Creates a new enrollment on the activity for the given user.
     */
    public function createEnrollment(Activity $activity, User $user): Enrollment;

    /**
     * Returns if the given enrollment can advance to the given state. If it's already on
     * or past said state, it should always return false.
     */
    public function canAdvanceTo(Enrollment $enrollment, string $target): bool;

    /**
     * Transitions states where possible.
     */
    public function advanceEnrollment(Activity $activity, Enrollment &$enrollment): void;

    /**
     * Transfers an enrollment to the new user, sending proper mails and invoicing jobs.
     */
    public function transferEnrollment(Enrollment $enrollment, User $reciever): Enrollment;
}
