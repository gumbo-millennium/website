<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\File;
use App\Models\FileDownload;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * Disallow most modifications to file download models (they're for logging and should not be modified).
 */
class FileDownloadPolicy
{
    // phpcs:disable SlevomatCodingStandard.Functions.UnusedParameter
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any file downloads.
     * @param  \App\Models\User  $user
     * @return mixed
     */
    public function viewAny(User $user)
    {
        return $user->can('viewAny', File::class);
    }

    /**
     * Determine whether the user can view the file download.
     * @param  \App\Models\User  $user
     * @param  \App\Models\FileDownload  $fileDownload
     * @return mixed
     */
    public function view(User $user, FileDownload $fileDownload)
    {
        return $user->can('viewAny', File::class);
    }

    /**
     * Determine whether the user can create file downloads.
     * @param  \App\Models\User  $user
     * @return mixed
     */
    public function create(User $user)
    {
        return false;
    }

    /**
     * Determine whether the user can update the file download.
     * @param  \App\Models\User  $user
     * @param  \App\Models\FileDownload  $fileDownload
     * @return mixed
     */
    public function update(User $user, FileDownload $fileDownload)
    {
        return false;
    }

    /**
     * Determine whether the user can delete the file download.
     * @param  \App\Models\User  $user
     * @param  \App\Models\FileDownload  $fileDownload
     * @return mixed
     */
    public function delete(User $user, FileDownload $fileDownload)
    {
        return false;
    }

    /**
     * Determine whether the user can restore the file download.
     * @param  \App\Models\User  $user
     * @param  \App\Models\FileDownload  $fileDownload
     * @return mixed
     */
    public function restore(User $user, FileDownload $fileDownload)
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the file download.
     * @param  \App\Models\User  $user
     * @param  \App\Models\FileDownload  $fileDownload
     * @return mixed
     */
    public function forceDelete(User $user, FileDownload $fileDownload)
    {
        return false;
    }
}
