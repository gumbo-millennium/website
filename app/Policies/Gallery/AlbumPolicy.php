<?php

declare(strict_types=1);

namespace App\Policies\Gallery;

use App\Enums\AlbumVisibility;
use App\Models\Activity;
use App\Models\Gallery\Album;
use App\Models\States\Enrollment as EnrollmentStates;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class AlbumPolicy
{
    use HandlesAuthorization;

    public const REASON_NO_ACCESS = "You don't have permission to use the gallery";

    public const REASON_BANNED = 'Your account has been blocked from making changes to the gallery';

    public const REASON_NOT_ALLOWED = "You don't have permission to make these changes";

    /**
     * Determine whether the user can view any models.
     *
     * @return bool|\Illuminate\Auth\Access\Response
     */
    public function viewAny(User $user): Response
    {
        if ($user->hasPermissionTo('gallery-use')) {
            return $this->allow();
        }

        return $this->deny(__(self::REASON_NO_ACCESS));
    }

    /**
     * Determine whether the user can view the model.
     *
     * @return bool|\Illuminate\Auth\Access\Response
     */
    public function view(User $user, Album $album): Response
    {
        if (! $user->hasPermissionTo('gallery-use')) {
            return $this->deny(__(self::REASON_NO_ACCESS));
        }

        if ($album->visibility === AlbumVisibility::Public) {
            return $this->allow();
        }

        if ($album->user?->is($user)) {
            return $this->allow();
        }

        if ($user->hasPermissionTo('gallery-manage')) {
            return $this->allow();
        }

        /*
        $activity = $album->activity;
        if ($activity) {
            $enrollmentExists = $user->enrollments()
                ->where('activity_id', $album->activity_id)
                ->whereState([
                    EnrollmentStates\Confirmed::class,
                    EnrollmentStates\Paid::class,
                ])
                ->exists();
            if ($enrollmentExists) {
                return $this->allow();
            }
        }
        */

        return $this->deny();
    }

    /**
     * Determine whether the user can create models.
     *
     * @return bool|\Illuminate\Auth\Access\Response
     */
    public function create(User $user): Response
    {
        return $this->deny("Being decomissioned");
    }

    /**
     * Determine whether the user can update the model.
     *
     * @return bool|\Illuminate\Auth\Access\Response
     */
    public function update(User $user, Album $album): Response
    {
        if ($user->hasPermissionTo('gallery-lock')) {
            return $this->deny(__(self::REASON_BANNED));
        }

        if ($user->hasPermissionTo('gallery-manage')) {
            return $this->allow();
        }

        if ($album->user?->is($user)) {
            return $this->allow();
        }

        $activity = $album->activity;
        if ($activity && $user->can('update', $activity)) {
            return $this->allow();
        }

        return $this->deny(__(self::REASON_NOT_ALLOWED));
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @return bool|\Illuminate\Auth\Access\Response
     */
    public function delete(User $user, Album $album): bool
    {
        return $user->can('update', $album);
    }

    public function upload(User $user, Album $album): Response
    {
        if ($user->can('update', $album)) {
            return $this->allow();
        }

        /*
        $activity = $album->activity;
        if ($activity) {
            $enrollmentExists = $user->enrollments()
                ->where('activity_id', $album->activity_id)
                ->whereState([
                    EnrollmentStates\Confirmed::class,
                    EnrollmentStates\Paid::class,
                ])
                ->exists();
            if ($enrollmentExists) {
                return $this->allow();
            }
        }
        */

        return $this->deny(__(self::REASON_NOT_ALLOWED));
    }

    /**
     * Determine whether the user can restore the model.Illuminate\Auth\Access\Response.
     */
    public function restore(User $user, Album $album): bool
    {
        return $user->hasPermissionTo('gallery-manage');
    }

    /**
     * @return bool|\Illuminate\Auth\Access\Response
     */
    public function forceDelete(User $user, Album $album): bool
    {
        return $user->hasPermissionTo('gallery-manage');
    }
}
