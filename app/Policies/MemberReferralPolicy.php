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
     */
    public function viewAny(User $user)
    {
        return $user->hasPermissionTo('referral-manage');
    }

    /**
     * Determine whether the user can view the member referral.
     */
    public function view(User $user, MemberReferral $memberReferral)
    {
        return $user->hasPermissionTo('referral-manage');
    }

    /**
     * Determine whether the user can create member referrals.
     */
    public function create(User $user)
    {
        return $user->hasPermissionTo('referral-manage');
    }

    /**
     * Determine whether the user can update the member referral.
     */
    public function update(User $user, MemberReferral $memberReferral)
    {
        return $user->hasPermissionTo('referral-manage');
    }

    /**
     * Determine whether the user can delete the member referral.
     */
    public function delete(User $user, MemberReferral $memberReferral)
    {
        return $user->hasPermissionTo('referral-manage');
    }

    /**
     * Determine whether the user can restore the member referral.
     */
    public function restore(User $user, MemberReferral $memberReferral)
    {
        return $user->hasPermissionTo('referral-manage');
    }

    /**
     * Determine whether the user can permanently delete the member referral.
     */
    public function forceDelete(User $user, MemberReferral $memberReferral)
    {
        return $user->hasPermissionTo('referral-manage');
    }

    /**
     * Determine whether the user can add the source user to the referral.
     */
    public function addUser(User $user, MemberReferral $memberReferral): bool
    {
        return $user->hasPermissionTo('referral-manage');
    }
}
