<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Sponsor;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class SponsorPolicy
{
    // phpcs:disable SlevomatCodingStandard.Functions.UnusedParameter
    use HandlesAuthorization;

    public const ADMIN_PERMISSION = 'sponsor-admin';

    /**
     * Determine whether the user can view any pages.
     *
     * @param  \App\Models\User  $user
     * @return mixed
     */
    public function viewAny(User $user)
    {
        return $user->can('manage', Sponsor::class);
    }

    /**
     * Determine whether the user can view the sponsor.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Sponsor  $sponsor
     * @return mixed
     */
    public function view(User $user, Sponsor $sponsor)
    {
        return $user->can('manage', Sponsor::class);
    }

    /**
     * Determine whether the user can create pages.
     *
     * @param  \App\Models\User  $user
     * @return mixed
     */
    public function create(User $user)
    {
        return $user->can('manage', Sponsor::class);
    }

    /**
     * Determine whether the user can update the sponsor.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Sponsor  $sponsor
     * @return mixed
     */
    public function update(User $user, Sponsor $sponsor)
    {
        return $user->can('manage', Sponsor::class);
    }

    /**
     * Determine whether the user can delete the sponsor.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Sponsor  $sponsor
     * @return mixed
     */
    public function delete(User $user, Sponsor $sponsor)
    {
        return $user->can('manage', Sponsor::class);
    }

    /**
     * Disallow restoring deleted items, since a 410 is permanent
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Sponsor  $sponsor
     * @return mixed
     */
    public function restore(User $user, Sponsor $sponsor)
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the sponsor.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Sponsor  $sponsor
     * @return mixed
     */
    public function forceDelete(User $user, Sponsor $sponsor)
    {
        return false;
    }

    /**
     * Returns if the user is allowed to edit sponsors articles.
     *
     * @param User $user
     * @return bool
     */
    public function manage(User $user)
    {
        return $user->hasPermissionTo(self::ADMIN_PERMISSION);
    }
}
