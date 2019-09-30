<?php

namespace App\Policies;

use App\Models\User;
use App\Models\File;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Support\Str;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Nova;

class FilePolicy
{
    use HandlesAuthorization;

    /**
     * @var string Permission name
     */
    public const USER_PERMISSION = 'file-view';
    public const ADMIN_PERMISSION = 'file-admin';

    /**
     * Determine whether the user can view any files.
     *
     * @param  \App\Models\User  $user
     * @return mixed
     */
    public function viewAny(User $user)
    {
        return $user->can('manage', File::class);
    }

    /**
     * Determine whether the user can view the file.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\File  $file
     * @return mixed
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function view(User $user, File $file)
    {
        return $user->can('manage', File::class);
    }

    /**
     * Determine whether the user can view the file.
     *
     * @param  \App\Models\User  $user
     * @return mixed
     */
    public function viewPublic(User $user)
    {
        return $user->hasPermissionTo(self::USER_PERMISSION);
    }

    /**
     * Determine whether the user can download the file.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\File  $file
     * @return mixed
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function download(User $user, File $file)
    {
        return $user->hasPermissionTo(self::USER_PERMISSION);
    }

    /**
     * Determine whether the user can create files.
     *
     * @param  \App\Models\User  $user
     * @return mixed
     */
    public function create(User $user)
    {
        return $user->can('manage', File::class);
    }

    /**
     * Determine whether the user can update the file.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\File  $file
     * @return mixed
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function update(User $user, File $file)
    {
        return $user->can('manage', File::class);
    }

    /**
     * Determine whether the user can delete the file.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\File  $file
     * @return mixed
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function delete(User $user, File $file)
    {
        return $user->can('manage', File::class);
    }

    /**
     * Determine whether the user can restore the file.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\File  $file
     * @return mixed
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function restore(User $user, File $file)
    {
        return $user->can('manage', File::class);
    }

    /**
     * Determine whether the user can permanently delete the file.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\File  $file
     * @return mixed
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function forceDelete(User $user, File $file)
    {
        return $user->can('manage', File::class);
    }

    /**
     * Can the given user manage the given activities or activities in general
     *
     * @param User $user
     * @return bool
     */
    public function manage(User $user): bool
    {
        return $user->hasPermissionTo(self::ADMIN_PERMISSION);
    }
}
