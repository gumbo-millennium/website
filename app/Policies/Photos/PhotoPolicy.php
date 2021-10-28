<?php

declare(strict_types=1);

namespace App\Policies\Photos;

use App\Enums\AlbumVisibility;
use App\Models\Photos\Album;
use App\Models\Photos\Photo;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PhotoPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any photos.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the photo.
     */
    public function view(User $user, Photo $photo): bool
    {
        return $photo->album->visibility == AlbumVisibility::WORLD
            || ($user && $user->can('view', $photo->album));
    }

    /**
     * Determine whether the user can create photos.
     */
    public function create(User $user): bool
    {
        return $user->can('create', Album::class);
    }

    /**
     * Determine whether the user can update the photo.
     */
    public function update(User $user, Photo $photo): bool
    {
        return $user->can('update', $photo->album);
    }

    /**
     * Determine whether the user can delete the photo.
     */
    public function delete(User $user, Photo $photo): bool
    {
        return $this->update($user, $photo);
    }
}
