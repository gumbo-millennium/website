<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\RedirectInstruction;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class RedirectInstructionPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any redirect instructions.
     */
    public function viewAny(User $user)
    {
        return $user->hasPermissionTo('redirects-manage');
    }

    /**
     * Determine whether the user can view the redirect instruction.
     *
     * @param \App\RedirectInstruction $redirectInstruction
     */
    public function view(User $user, RedirectInstruction $redirectInstruction)
    {
        return $user->hasPermissionTo('redirects-manage');
    }

    /**
     * Determine whether the user can create redirect instructions.
     */
    public function create(User $user)
    {
        return $user->hasPermissionTo('redirects-manage');
    }

    /**
     * Determine whether the user can update the redirect instruction.
     *
     * @param \App\RedirectInstruction $redirectInstruction
     */
    public function update(User $user, RedirectInstruction $redirectInstruction)
    {
        return $user->hasPermissionTo('redirects-manage');
    }

    /**
     * Determine whether the user can delete the redirect instruction.
     *
     * @param \App\RedirectInstruction $redirectInstruction
     */
    public function delete(User $user, RedirectInstruction $redirectInstruction)
    {
        return $user->hasPermissionTo('redirects-manage');
    }

    /**
     * Determine whether the user can restore the redirect instruction.
     *
     * @param \App\RedirectInstruction $redirectInstruction
     */
    public function restore(User $user, RedirectInstruction $redirectInstruction)
    {
        return $user->hasPermissionTo('redirects-manage');
    }

    /**
     * Determine whether the user can permanently delete the redirect instruction.
     *
     * @param \App\RedirectInstruction $redirectInstruction
     */
    public function forceDelete(User $user, RedirectInstruction $redirectInstruction)
    {
        return false;
    }
}
