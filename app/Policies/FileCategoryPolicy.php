<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\FileBundle;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class FileCategoryPolicy
{
    // phpcs:disable SlevomatCodingStandard.Functions.UnusedParameter
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any file categories.
     */
    public function viewAny(User $user)
    {
        return $user->can('viewAny', FileBundle::class);
    }

    /**
     * Determine whether the user can view the file category.
     */
    public function view(User $user)
    {
        return $user->can('viewAny', FileBundle::class);
    }

    /**
     * Determine whether the user can create file categories.
     */
    public function create(User $user)
    {
        return $user->can('manage', FileBundle::class);
    }

    /**
     * Determine whether the user can update the file category.
     */
    public function update(User $user)
    {
        return $user->can('manage', FileBundle::class);
    }

    /**
     * Determine whether the user can delete the file category.
     */
    public function delete(User $user)
    {
        return $user->can('manage', FileBundle::class);
    }

    /**
     * Determine whether the user can restore the file category.
     */
    public function restore(User $user)
    {
        return $user->can('manage', FileBundle::class);
    }

    /**
     * Determine whether the user can permanently delete the file category.
     */
    public function forceDelete(User $user)
    {
        return $user->can('manage', FileBundle::class);
    }
}
