<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\EmailList;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * Email List policy, these models are read-only.
 */
class EmailListPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any email lists.
     */
    public function viewAny(User $user)
    {
        return $user->hasPermissionTo('user-admin');
    }

    /**
     * Determine whether the user can view the email list.
     */
    public function view(User $user, EmailList $emailList)
    {
        return $user->hasPermissionTo('user-admin');
    }

    /**
     * Determine whether the user can create email lists.
     */
    public function create(User $user)
    {
        return false;
    }

    /**
     * Determine whether the user can update the email list.
     */
    public function update(User $user, EmailList $emailList)
    {
        return false;
    }

    /**
     * Determine whether the user can delete the email list.
     */
    public function delete(User $user, EmailList $emailList)
    {
        return false;
    }

    /**
     * Determine whether the user can restore the email list.
     */
    public function restore(User $user, EmailList $emailList)
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the email list.
     */
    public function forceDelete(User $user, EmailList $emailList)
    {
        return false;
    }
}
