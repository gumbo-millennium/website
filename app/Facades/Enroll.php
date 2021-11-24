<?php

declare(strict_types=1);

namespace App\Facades;

use App\Contracts\EnrollmentServiceContract;
use Illuminate\Support\Facades\Facade;

/**
 * @method static null|Enrollment getEnrollment(Activity $activity)
 * @method static array findTicketsForActivity(Activity $activity)
 * @method static bool canEnroll(Activity $activity)
 * @method static Enrollment transferEnrollment(Enrollment $enrollment, User $reciever)
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
