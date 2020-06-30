<?php

declare(strict_types=1);

namespace App\Contracts;

use App\Models\Activity;
use App\Models\Enrollment;
use App\Models\User;
use Illuminate\Contracts\Cache\Lock;

interface EnrollmentServiceContract
{
    /**
     * Returns true if the service uses locks and apps should support it
     * @return bool
     */
    public function useLocks(): bool;

    /**
     * Returns a lock to enroll the given user
     * @param Activity $activity
     * @return Lock
     * @throws \LogicException if locsk are not supported
     */
    public function getLock(Activity $activity): Lock;

    /**
     * Returns true if the activity allows new enrollments and the user is allowed to enroll (if given)
     * @param Activity $activity
     * @param null|User $user
     * @return bool
     */
    public function canEnroll(Activity $activity, ?User $user): bool;

    /**
     * Creates a new enrollment on the activity for the given user
     * @param Activity $activity
     * @param User $user
     * @return Enrollment
     */
    public function createEnrollment(Activity $activity, User $user): Enrollment;

    /**
     * Transitions states where possible
     * @param Activity $activity
     * @param Enrollment $enrollment
     * @return void
     */
    public function advanceEnrollment(Activity $activity, Enrollment &$enrollment): void;


    /**
     * Transfers an enrollment to the new user, sending proper mails and invoicing jobs
     * @param Enrollment $enrollment
     * @param User $reciever
     * @return Enrollment
     */
    public function transferEnrollment(Enrollment $enrollment, User $reciever): Enrollment;
}
