<?php

namespace App\Policies;

use App\File;
use App\User;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * Handles policies for viewing, downloading and managing files.
 *
 * @author Roelof Roos <github@roelof.io>
 * @license MPL-2.0
 */
class FilePolicy
{
    use HandlesAuthorization;

    /**
     * Checks if the given user is allowed to mutate the given file using the
     * given permission, adding the 'file-publish' permission if the file is public.
     *
     * @param User $user User to verify
     * @param File $file File to check against
     * @param array $permissions Permission required
     * @return bool True if allowed
     */
    protected function grantedWithPublic(User $user, File $file, array $permissions) : bool
    {
        // If the file is public, you need to have permission to un-publish it.
        if ($file->public) {
            $permissions[] = 'file-publish';
        }

        // Otherwise, you just need the normal one
        return $user->hasAllPermissions($permissions);
    }

    /**
     * Determine whether the user can manage files
     *
     * @param  \App\User  $user
     * @return mixed
     */
    public function manage(User $user)
    {
        return $user->hasAnyPermission([
            'file-add',
            'file-edit',
            'file-delete'
        ]);
    }

    /**
     * Determine whether the user can view the file.
     *
     * @param  \App\User  $user
     * @param  \App\File  $file
     * @return mixed
     */
    public function view(User $user, File $file)
    {
        return $user->hasPermissionTo('file-view');
    }

    /**
     * Determine whether the user can view the file.
     *
     * @param  \App\User  $user
     * @param  \App\File  $file
     * @return mixed
     */
    public function download(User $user, File $file)
    {
        return $user->hasPermissionTo('file-download');
    }

    /**
     * Determine whether the user can create files.
     *
     * @param  \App\User  $user
     * @return mixed
     */
    public function create(User $user)
    {
        return $user->hasPermissionTo('file-add');
    }

    /**
     * Determine whether the user can update the file.
     *
     * @param  \App\User  $user
     * @param  \App\File  $file
     * @return mixed
     */
    public function update(User $user, File $file)
    {
        return $this->grantedWithPublic($user, $file, ['file-edit']);
    }

    /**
     * Returns if the user can (un)publish this file
     *
     * @param User $user
     * @return mixed
     */
    public function publish(User $user)
    {
        return $user->hasPermissionTo('file-publish');
    }

    /**
     * Determine whether the user can delete the file.
     *
     * @param  \App\User  $user
     * @param  \App\File  $file
     * @return mixed
     */
    public function delete(User $user, File $file)
    {
        return $this->grantedWithPublic($user, $file, ['file-delete']);
    }

    /**
     * Determine whether the user can restore the file.
     *
     * @param  \App\User  $user
     * @param  \App\File  $file
     * @return mixed
     */
    public function restore(User $user, File $file)
    {
        return $this->grantedWithPublic($user, $file, ['file-add', 'file-edit']);
    }

    /**
     * Determine whether the user can permanently delete the file.
     *
     * @param  \App\User  $user
     * @param  \App\File  $file
     * @return mixed
     */
    public function forceDelete(User $user, File $file)
    {
        return $this->grantedWithPublic($user, $file, ['file-force-delete']);
    }
}
