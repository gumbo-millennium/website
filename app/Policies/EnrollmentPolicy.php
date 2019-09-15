<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Enrollment;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Database\Eloquent\Builder;

class EnrollmentPolicy
{
    use HandlesAuthorization;

    public static function hasEnrollmentPermissions(User $user) : bool
    {
        return $user->hasAnyPermission([
            'enrollment-create',
            'enrollment-update',
            'enrollment-refund'
        ]);
    }

    /**
     * Determine whether the user can view any enrollments.
     *
     * @param  \App\Models\User  $user
     * @return mixed
     */
    public function viewAny(User $user)
    {
        // User is allowed by system
        if ($user->hasAnyPermission([
            'enrollment-create',
            'enrollment-update',
            'enrollment-refund'
        ])) {
            return true;
        }

        // User has one or more activities he/she manages
        return Activity::query()
            ->where('user_id', $user->id)
            ->whereIn('role_id', $user->roles->pluck('id'))
            ->count() > 0;
    }

    /**
     * Determine whether the user can view the enrollment.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Enrollment  $enrollment
     * @return mixed
     */
    public function view(User $user, Enrollment $enrollment)
    {
        return $user->hasAnyPermission(['enrollment-update', 'enrollment-refund'])
            || ActivityPolicy::isOwningUser($user, $enrollment->activity);
    }

    /**
     * Determine whether the user can create enrollments.
     *
     * @param  \App\Models\User  $user
     * @return mixed
     */
    public function create(User $user)
    {
        return $user->hasPermissionTo('enrollment-create')
            || ActivityPolicy::hasAnyActivity($user);
    }

    /**
     * Determine whether the user can update the enrollment.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Enrollment  $enrollment
     * @return mixed
     */
    public function update(User $user, Enrollment $enrollment)
    {
        return $user->hasPermissionTo('enrollment-update')
            || ActivityPolicy::isOwningUser($user, $enrollment->activity);
    }

    /**
     * Determine whether the user can refund the money paid for the enrollment.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Enrollment  $enrollment
     * @return mixed
     */
    public function refund(User $user, Enrollment $enrollment)
    {
        return $user->hasPermissionTo('enrollment-refund')
            || ActivityPolicy::isOwningUser($user, $enrollment->activity);
    }

    /**
     * Determine whether the user can delete the enrollment.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Enrollment  $enrollment
     * @return mixed
     */
    public function delete(User $user, Enrollment $enrollment)
    {
        return $user->hasPermissionTo('enrollment-delete')
            || ActivityPolicy::isOwningUser($user, $enrollment->activity);
    }
}
