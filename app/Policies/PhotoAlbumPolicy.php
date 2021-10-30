<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\AlbumVisibility;
use App\Models\PhotoAlbum;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PhotoAlbumPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any albums.
     * Type hint User as optional to ensure guests are granted too.
     *
     * @param null|User $user User to check, must be nullable /and/ present
     */
    public function viewAny(?User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the album.
     */
    public function view(?User $user, PhotoAlbum $album): bool
    {
        switch ($album->visibility) {
            case AlbumVisibility::WORLD:
                return true;
            case AlbumVisibility::HIDDEN:
                return $user && (
                    $user->id === $album->user_id
                    || $user->hasPermissionTo('photo-album-admin')
                );

            case AlbumVisibility::USERS:
                return $user !== null;
            case AlbumVisibility::MEMBERS_ONLY:
                return $user && $user->is_member;
            default:
                return false;
        }
    }

    /**
     * Determine whether the user can create albums.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('photo-album-edit');
    }

    /**
     * Determine whether the user can update the album.
     */
    public function update(User $user, PhotoAlbum $album): bool
    {
        return ($user->hasPermissionTo('photo-album-edit') && $user->is($album->user))
            || ($user->hasPermissionTo('photo-album-admin'));
    }

    /**
     * Determine whether the user can delete the album.
     */
    public function delete(User $user, PhotoAlbum $album): bool
    {
        return $this->update($user, $album);
    }
}
