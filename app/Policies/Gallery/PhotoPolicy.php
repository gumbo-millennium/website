<?php

declare(strict_types=1);

namespace App\Policies\Gallery;

use App\Enums\PhotoVisibility;
use App\Models\Gallery\Album;
use App\Models\Gallery\Photo;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class PhotoPolicy
{
    use HandlesAuthorization;

    private const REASON_NOT_FOR_YOU = "You're not allowed to view this photo";

    private const REASON_NO_SELF_REPORT = "You can't report your own photos or photos in albums you own";

    private const REASON_NO_DOUBLE_REPORT = "You've already reported this photo";

    /**
     * Determine whether the user can view any models.
     *
     * @return bool|\Illuminate\Auth\Access\Response
     */
    public function viewAny(User $user): Response
    {
        if ($user->hasPermissionTo('gallery-use')) {
            return $this->allow();
        }

        return $this->deny(__(AlbumPolicy::REASON_NO_ACCESS));
    }

    /**
     * Determine whether the user can view the model.
     *
     * @return bool|\Illuminate\Auth\Access\Response
     */
    public function view(User $user, Photo $photo): Response
    {
        if (! $user->hasPermissionTo('gallery-use')) {
            return $this->deny(__(AlbumPolicy::REASON_NO_ACCESS));
        }

        if ($photo->visibility === PhotoVisibility::Visible) {
            return $this->allow();
        }

        if ($photo->user?->is($user)) {
            return $this->allow();
        }

        return $this->deny(__(self::REASON_NOT_FOR_YOU)) ;
    }

    /**
     * Determine whether the user can create models.
     *
     * @return bool|\Illuminate\Auth\Access\Response
     */
    public function create(User $user)
    {
        return $this->deny("Being decomissioned");
    }

    /**
     * Determine whether the user can update the model.
     *
     * @return bool|\Illuminate\Auth\Access\Response
     */
    public function update(User $user, Photo $photo): Response
    {
        // Allow if the user can edit this album
        if ($user->can('update', $photo->album)) {
            return $this->allow();
        }

        // Allow if user is owner
        if ($photo->user?->is($user)) {
            return $this->allow();
        }

        return $this->deny(__(AlbumPolicy::REASON_NOT_ALLOWED));
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @return bool|\Illuminate\Auth\Access\Response
     */
    public function delete(User $user, Photo $photo): Response
    {
        return $this->update($user, $photo)
            ? $this->allow()
            : $this->deny(__(AlbumPolicy::REASON_NOT_ALLOWED));
    }

    public function react(User $user, Photo $photo): Response
    {
        // Can't report if you can't see it
        if ($user->can('view', $photo)) {
            return $this->allow();
        }

        return $this->deny(__(self::REASON_NOT_FOR_YOU));
    }

    /**
     * Determine if the user is allowed to report this photo.
     * @return bool
     */
    public function report(User $user, Photo $photo): Response
    {
        // Can't report if you can't see it
        if (! $user->can('view', $photo)) {
            return $this->deny(__(self::REASON_NOT_FOR_YOU));
        }

        // Can't report if you can edit the photo
        if ($user->can('update', $photo)) {
            return $this->deny(__(self::REASON_NO_SELF_REPORT));
        }

        // Can't double-report
        $hasReportedPhoto = $photo->reports()
            ->whereHas('user', fn ($query) => $query->where('id', $user->id))
            ->exists();
        if (! $hasReportedPhoto) {
            return $this->allow();
        }

        return $this->deny(__(self::REASON_NO_DOUBLE_REPORT));
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @return bool|\Illuminate\Auth\Access\Response
     */
    public function restore(User $user, Photo $photo)
    {
        if ($user->hasPermissionTo('gallery-manage')) {
            return $this->allow();
        }

        return $this->deny(__(AlbumPolicy::REASON_NOT_ALLOWED));
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @return bool|\Illuminate\Auth\Access\Response
     */
    public function forceDelete(User $user, Photo $photo)
    {
        return $this->restore($user, $photo);
    }
}
