<?php

namespace App\Policies;

use App\Models\File;
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
        return $user->can('viewAny', File::class);
    }

    /**
     * Determine whether the user can view the file category.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\FileCategory  $fileCategory
     * @return mixed
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function view(User $user, FileCategory $fileCategory)
    {
        return $user->can('viewAny', File::class);
    }

    /**
     * Determine whether the user can create file categories.
     *
     * @param  \App\Models\User  $user
     * @return mixed
     */
    public function create(User $user)
    {
        return $user->can('manage', File::class);
    }

    /**
     * Determine whether the user can update the file category.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\FileCategory  $fileCategory
     * @return mixed
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function update(User $user, FileCategory $fileCategory)
    {
        return $user->can('manage', File::class);
    }

    /**
     * Determine whether the user can delete the file category.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\FileCategory  $fileCategory
     * @return mixed
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function delete(User $user, FileCategory $fileCategory)
    {
        return $user->can('manage', File::class);
    }

    /**
     * Determine whether the user can restore the file category.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\FileCategory  $fileCategory
     * @return mixed
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function restore(User $user, FileCategory $fileCategory)
    {
        return $user->can('manage', File::class);
    }

    /**
     * Determine whether the user can permanently delete the file category.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\FileCategory  $fileCategory
     * @return mixed
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function forceDelete(User $user, FileCategory $fileCategory)
    {
        return $user->can('manage', File::class);
    }
}
