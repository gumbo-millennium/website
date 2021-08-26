<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\FileBundle;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class FileBundlePolicy
{
    use HandlesAuthorization;

    public const USER_PERMISSION = 'file-view';

    public const ADMIN_PERMISSION = 'file-admin';

    /**
     * Determine whether the user can view any files.
     */
    public function viewAny(User $user)
    {
        return $user->can('manage', FileBundle::class);
    }

    /**
     * Determine whether the user can view the file.
     *
     * @param \App\Models\File $file
     */
    public function view(User $user, FileBundle $file)
    {
        return $user->can('manage', FileBundle::class);
    }

    /**
     * Determine whether the user can view the file.
     */
    public function viewPublic(User $user)
    {
        return $user->hasPermissionTo(self::USER_PERMISSION);
    }

    /**
     * Determine whether the user can download the file.
     *
     * @param \App\Models\File $file
     */
    public function download(User $user, FileBundle $file)
    {
        return $user->hasPermissionTo(self::USER_PERMISSION);
    }

    /**
     * Determine whether the user can create files.
     */
    public function create(User $user)
    {
        return $user->can('manage', FileBundle::class);
    }

    /**
     * Determine whether the user can update the file.
     *
     * @param \App\Models\File $file
     */
    public function update(User $user, FileBundle $file)
    {
        return $user->can('manage', FileBundle::class);
    }

    /**
     * Determine whether the user can delete the file.
     *
     * @param \App\Models\File $file
     */
    public function delete(User $user, FileBundle $file)
    {
        return $user->can('manage', FileBundle::class);
    }

    /**
     * Determine whether the user can restore the file.
     *
     * @param \App\Models\File $file
     */
    public function restore(User $user, FileBundle $file)
    {
        return $user->can('manage', FileBundle::class);
    }

    /**
     * Determine whether the user can permanently delete the file.
     *
     * @param \App\Models\File $file
     */
    public function forceDelete(User $user, FileBundle $file)
    {
        return $user->can('manage', FileBundle::class);
    }

    /**
     * Can the given user manage the given activities or activities in general.
     */
    public function manage(User $user): bool
    {
        return $user->hasPermissionTo(self::ADMIN_PERMISSION);
    }
}
