<?php

namespace App\Policies;

use App\Models\User;
use App\Models\FileDownload;
use Illuminate\Auth\Access\HandlesAuthorization;

class FileDownloadPolicy
{
    use HandlesAuthorization;
    
    /**
     * Determine whether the user can view any file downloads.
     *
     * @param  \App\Models\User  $user
     * @return mixed
     */
    public function viewAny(User $user)
    {
        //
    }

    /**
     * Determine whether the user can view the file download.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\FileDownload  $fileDownload
     * @return mixed
     */
    public function view(User $user, FileDownload $fileDownload)
    {
        //
    }

    /**
     * Determine whether the user can create file downloads.
     *
     * @param  \App\Models\User  $user
     * @return mixed
     */
    public function create(User $user)
    {
        //
    }

    /**
     * Determine whether the user can update the file download.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\FileDownload  $fileDownload
     * @return mixed
     */
    public function update(User $user, FileDownload $fileDownload)
    {
        //
    }

    /**
     * Determine whether the user can delete the file download.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\FileDownload  $fileDownload
     * @return mixed
     */
    public function delete(User $user, FileDownload $fileDownload)
    {
        //
    }

    /**
     * Determine whether the user can restore the file download.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\FileDownload  $fileDownload
     * @return mixed
     */
    public function restore(User $user, FileDownload $fileDownload)
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the file download.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\FileDownload  $fileDownload
     * @return mixed
     */
    public function forceDelete(User $user, FileDownload $fileDownload)
    {
        //
    }
}
