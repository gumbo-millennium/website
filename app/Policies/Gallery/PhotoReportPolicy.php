<?php

declare(strict_types=1);

namespace App\Policies\Gallery;

use App\Models\Gallery\PhotoReport;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Support\Facades\Date;

class PhotoReportPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     *
     * @return bool|\Illuminate\Auth\Access\Response
     */
    public function viewAny(User $user)
    {
        if ($user->hasPermissionTo('gallery-manage')) {
            return $this->allow();
        }

        return $this->deny(__(AlbumPolicy::REASON_NOT_ALLOWED));
    }

    /**
     * Determine whether the user can view the model.
     *
     * @return bool|\Illuminate\Auth\Access\Response
     */
    public function view(User $user, PhotoReport $photoReport)
    {
        if ($user->hasPermissionTo('gallery-manage')) {
            return $this->allow();
        }

        if ($photoReport->user?->is($user)) {
            return $this->allow();
        }

        return $this->deny(__(AlbumPolicy::REASON_NOT_ALLOWED));
    }

    /**
     * Determine whether the user can create models.
     *
     * @return bool|\Illuminate\Auth\Access\Response
     */
    public function create(User $user)
    {
        if ($user->hasPermissionTo('gallery-manage')) {
            return $this->allow();
        }

        return $this->deny();
    }

    /**
     * Determine whether the user can update the model.
     *
     * @return bool|\Illuminate\Auth\Access\Response
     */
    public function update(User $user, PhotoReport $photoReport)
    {
        // Can't interact with own reports
        if ($photoReport->user?->is($user)) {
            return $this->deny(__("You're not allowed to alter reports you made yourself"));
        }

        // Require manager permissions
        if (! $user->hasPermissionTo('gallery-manage')) {
            return $this->deny(__(AlbumPolicy::REASON_NOT_ALLOWED));
        }

        // Require that the report is not resolved
        if ($photoReport->is_resolved) {
            return $this->deny(__("You're not allowed to change resolved reports"));
        }

        // Can't interact with trashed resources
        if ($photoReport->trashed()) {
            return $this->deny(__("You're not allowed to change deleted reports"));
        }

        // Allow changes
        return $this->allow();
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @return bool|\Illuminate\Auth\Access\Response
     */
    public function delete(User $user, PhotoReport $photoReport)
    {
        // Disalllow removing self-reports
        if ($photoReport->user?->is($user)) {
            return $this->deny();
        }

        // Require the report to be resolved for at least 30 days
        if ($photoReport->is_resolved || $photoReport->resolved_at->isAfter(Date::today()->subDays(30))) {
            return $this->deny(__(AlbumPolicy::REASON_NOT_ALLOWED));
        }

        // Require manager permissions
        if (! $user->hasPermissionTo('gallery-manage')) {
            return $this->deny(__(AlbumPolicy::REASON_NOT_ALLOWED));
        }

        // Allow removal
        return $this->allow();
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @return bool|\Illuminate\Auth\Access\Response
     */
    public function restore(User $user, PhotoReport $photoReport)
    {
        // Disallow recovering deleted reports
        return $this->deny();
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @return bool|\Illuminate\Auth\Access\Response
     */
    public function forceDelete(User $user, PhotoReport $photoReport)
    {
        // Forbid permanently removing reports
        return $this->deny();
    }
}
