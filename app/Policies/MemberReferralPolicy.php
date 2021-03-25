<?php

declare(strict_types=1);

namespace App\Policies;

use App\MemberReferral;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class MemberReferralPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any member referrals.
     *
     * @param  \App\Models\User  $user
     * @return mixed
     */
    public function viewAny(User $user)
    {
        return $user->hasPermissionTo('referral-manage');
    }

    /**
     * Determine whether the user can view the member referral.
     *
     * @param  \App\Models\User  $user
     * @param  \App\MemberReferral  $memberReferral
     * @return mixed
     */
    public function view(User $user, MemberReferral $memberReferral)
    {
        return $user->hasPermissionTo('referral-manage');
    }

    /**
     * Determine whether the user can create member referrals.
     *
     * @param  \App\Models\User  $user
     * @return mixed
     */
    public function create(User $user)
    {
        return $user->hasPermissionTo('referral-manage');
    }

    /**
     * Determine whether the user can update the member referral.
     *
     * @param  \App\Models\User  $user
     * @param  \App\MemberReferral  $memberReferral
     * @return mixed
     */
    public function update(User $user, MemberReferral $memberReferral)
    {
        return $user->hasPermissionTo('referral-manage');
    }

    /**
     * Determine whether the user can delete the member referral.
     *
     * @param  \App\Models\User  $user
     * @param  \App\MemberReferral  $memberReferral
     * @return mixed
     */
    public function delete(User $user, MemberReferral $memberReferral)
    {
        return $user->hasPermissionTo('referral-manage');
    }

    /**
     * Determine whether the user can restore the member referral.
     *
     * @param  \App\Models\User  $user
     * @param  \App\MemberReferral  $memberReferral
     * @return mixed
     */
    public function restore(User $user, MemberReferral $memberReferral)
    {
        return $user->hasPermissionTo('referral-manage');
    }

    /**
     * Determine whether the user can permanently delete the member referral.
     *
     * @param  \App\Models\User  $user
     * @param  \App\MemberReferral  $memberReferral
     * @return mixed
     */
    public function forceDelete(User $user, MemberReferral $memberReferral)
    {
        return $user->hasPermissionTo('referral-manage');
    }

    /**
     * Determine whether the user can add the source user to the referral.
     *
     * @param User $user
     * @param MemberReferral $podcast
     * @return bool
     */
    public function addUser(User $user, MemberReferral $memberReferral): bool
    {
        return $user->hasPermissionTo('referral-manage');
    }
}
