<?php

declare(strict_types=1);

namespace Tests\Traits;

use App\Models\Activity;
use App\Models\Enrollment;
use App\Models\Role;
use App\Models\User;
use DateTimeInterface;
use Illuminate\Support\Facades\Date;

trait CreatesDummyActivityModels
{
    public function createDummyActivity(
        ?string $role = null,
        bool $public = true,
        ?DateTimeInterface $endDate = null
    ): Activity {
        $endDate = Date::make($endDate ?? \now()->addWeek());
        $startDate = (clone $endDate)->subHours(2);

        return \factory(Activity::class, 1)->create([
            'role_id' => $role ? Role::findByName($role)->id : null,
            'is_public' => $public,
            'seats' => 40,
            'cancelled_at' => null,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'enrollment_start' => null,
            'enrollment_end' => null,
        ])->first();
    }

    public function createDummyEnrollment(User $user, ?Activity $activity = null): Enrollment
    {
        // Ensure activity
        $activity ??= $this->createDummyActivity();

        // Create enrollment
        $enroll = $activity->enrollments()->make();
        $enroll->user_id = $user->id;
        $enroll->save();

        // Returns enrollment
        return $enroll;
    }
}
