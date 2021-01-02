<?php

declare(strict_types=1);

namespace App\ViewModels;

use App\Models\Activity;
use App\Models\Enrollment;
use App\Models\States\Enrollment\Cancelled;
use App\Models\States\Enrollment\Paid;
use App\Models\User;

class ActivityViewModel extends GumboViewModel
{
    /**
     * @var Activity $activity
     */
    public $activity;

    /**
     * @var Enrollment|false
     */
    public $enrollment;

    /**
     * @var User|null $user
     */
    protected $user;

    /**
     * Creates a new Activity Model for the given user
     *
     * @param Activity $activity
     */
    public function __construct(?User $user, Activity $activity)
    {
        $this->user = $user;
        $this->activity = $activity;
        $this->enrollment = $this->getEnrollment();
    }

    /**
     * Returns the user's enrollment. Should be just one
     *
     * @return Enrollment|null
     */
    protected function getEnrollment(): ?Enrollment
    {
        // Return local-cached value
        if ($this->enrollment !== null) {
            return $this->enrollment ?: null;
        }

        // Anonymous users can't be enrolled
        if (!$this->user) {
            $this->enrollment = false;
            return null;
        }

        // Find it
        return $this->enrollment = $this->activity->enrollments()
            ->where('user_id', $this->user->id)
            ->whereNotState('state', Cancelled::class)
            ->first();
    }

    /**
     * Handles checking if a user is enrolled
     *
     * @return bool
     */
    protected function getIsEnrolledAttribute(): bool
    {
        return $this->getEnrollment() !== null;
    }

    /**
     * Handles checking if a user paid for this enrollment
     *
     * @return bool
     */
    protected function getHasPaidAttribute(): bool
    {
        $enrollment = $this->getEnrollment();
        return $enrollment ? $enrollment->state->is(Paid::class) : false;
    }

    /**
     * Returns if the user needs to pay for this enrollment
     *
     * @return bool
     */
    protected function getIsPaidAttribute(): bool
    {
        // Check what price we need to get
        $member = optional($this->user)->is_member;

        // Check if true-ish (non-zero and not null)
        return ($member ? $this->activity->is_free_for_members : $this->activity->is_free) === true;
    }

    /**
     * Returns if the user is enrolled and the enrollment is in a non-volatile state.
     *
     * @return bool
     */
    protected function getIsStableAttribute(): bool
    {
        return $this->enrollment && $this->enrollment->is_stable;
    }
}
