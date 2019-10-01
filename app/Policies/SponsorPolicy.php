<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Sponsor;
use Illuminate\Auth\Access\HandlesAuthorization;

class SponsorPolicy
{
    use HandlesAuthorization;

    /**
     * @var string Permission name
     */
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
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
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
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
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
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function delete(User $user, Sponsor $sponsor)
    {
        return $user->can('manage', Sponsor::class);
    }

    /**
     * Determine whether the user can restore the sponsor.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Sponsor  $sponsor
     * @return mixed
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function restore(User $user, Sponsor $sponsor)
    {
        return $user->can('manage', Sponsor::class);
    }

    /**
     * Determine whether the user can permanently delete the sponsor.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Sponsor  $sponsor
     * @return mixed
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function forceDelete(User $user, Sponsor $sponsor)
    {
        return $user->can('manage', Sponsor::class);
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
