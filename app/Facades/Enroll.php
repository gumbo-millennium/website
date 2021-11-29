<?php

declare(strict_types=1);

namespace App\Facades;

use App\Contracts\EnrollmentServiceContract;
use App\Models\Activity;
use App\Models\Enrollment;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Facade;

/**
 * @method static null|Enrollment getEnrollment(Activity $activity)
 * @method static Collection<Enrollment> findTicketsForActivity(Activity $activity)
 * @method static bool canEnroll(Activity $activity)
 * @method static Enrollment transferEnrollment(Enrollment $enrollment, User $reciever)
 * @method static Enrollment createEnrollment(Activity $activity, Ticket $ticket)
 * @see \App\Contracts\EnrollmentServiceContract
 * @see \App\Services\EnrollmentService
 */
class Enroll extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return EnrollmentServiceContract::class;
    }
}
