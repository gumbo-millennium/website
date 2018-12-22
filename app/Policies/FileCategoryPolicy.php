<?php

namespace App\Policies;

use App\User;
use App\FileCategory;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * Handles authorisation of managing file categories
 *
 * @author Roelof Roos <github@roelof.io>
 * @license MPL-2.0
 */
class FileCategoryPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can create file categories.
     *
     * @param  \App\User  $user
     * @return mixed
     */
    public function browse(User $user)
    {
        return $user->hasPermissionTo('file-view');
    }

    /**
     * Determine whether the user can create file categories.
     *
     * @param  \App\User  $user
     * @return mixed
     */
    public function create(User $user)
    {
        return $user->hasPermissionTo('file-category-add');
    }

    /**
     * Determine whether the user can update the file category.
     *
     * @param  \App\User  $user
     * @param  \App\FileCategory  $fileCategory
     * @return mixed
     */
    public function update(User $user, FileCategory $fileCategory)
    {
        return $user->hasPermissionTo('file-category-edit');
    }

    /**
     * Determine whether the user can delete the file category.
     *
     * @param  \App\User  $user
     * @param  \App\FileCategory  $fileCategory
     * @return mixed
     */
    public function delete(User $user, FileCategory $fileCategory)
    {
        return $user->hasPermissionTo('file-category-delete');
    }
}
