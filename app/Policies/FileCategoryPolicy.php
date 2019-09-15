<?php

namespace App\Policies;

use App\Models\User;
use App\Models\FileCategory;
use Illuminate\Auth\Access\HandlesAuthorization;

class FileCategoryPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any file categories.
     *
     * @param  \App\Models\User  $user
     * @return mixed
     */
    public function viewAny(User $user)
    {
        return $user->hasPermissionTo('file-view');
    }

    /**
     * Determine whether the user can view the file category.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\FileCategory  $fileCategory
     * @return mixed
     */
    public function view(User $user, FileCategory $fileCategory)
    {
        return $user->hasPermissionTo('file-view');
    }

    /**
     * Determine whether the user can create file categories.
     *
     * @param  \App\Models\User  $user
     * @return mixed
     */
    public function create(User $user)
    {
        return $user->hasPermissionTo('file-create');
    }

    /**
     * Determine whether the user can update the file category.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\FileCategory  $fileCategory
     * @return mixed
     */
    public function update(User $user, FileCategory $fileCategory)
    {
        return $user->hasPermissionTo('file-update');
    }

    /**
     * Determine whether the user can delete the file category.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\FileCategory  $fileCategory
     * @return mixed
     */
    public function delete(User $user, FileCategory $fileCategory)
    {
        return $user->hasPermissionTo('file-delete');
    }

    /**
     * Determine whether the user can restore the file category.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\FileCategory  $fileCategory
     * @return mixed
     */
    public function restore(User $user, FileCategory $fileCategory)
    {
        return $user->hasAnyPermission('file-update', 'file-create');
    }

    /**
     * Determine whether the user can permanently delete the file category.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\FileCategory  $fileCategory
     * @return mixed
     */
    public function forceDelete(User $user, FileCategory $fileCategory)
    {
        return $user->hasPermissionTo('file-delete');
    }
}
