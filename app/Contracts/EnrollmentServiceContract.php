<?php

declare(strict_types=1);

namespace App\Contracts;

use App\Models\Activity;
use App\Models\Enrollment;
use App\Models\Ticket;
use App\Models\User;

interface EnrollmentServiceContract
{
    /**
     * Returns the current active enrollment for this activity for the current
     * user. If no user is logged in, returns null.
     */
    public function getEnrollment(Activity $activity): ?Enrollment;

    /**
     * Returns a list of tickets available for the current user for the given
     * activity.  If no user is logged in, a non-member user will be assumed.
     *
     * @return array<Ticket>
     */
    public function findTicketsForActivity(Activity $activity): array;

    /**
     * Checks if the user can enroll in this activity.  If no user is logged
     * in, a non-member user will be assumed.
     */
    public function canEnroll(Activity $activity): bool;

    /**
     * Transfers an enrollment to the new user, sending proper mails and
     * invoicing jobs.
     */
    public function transferEnrollment(Enrollment $enrollment, User $reciever): Enrollment;
}
