<?php

declare(strict_types=1);

namespace App\Policies\Minisite;

use App\Models\Minisite\Site;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class SitePolicy
{
    use HandlesAuthorization;

    /**
     * Returns true if the user can manage all content.
     */
    public function admin(User $user): bool
    {
        return $user->hasPermissionTo('content-admin');
    }

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): Response|bool
    {
        if ($user->can('admin', Site::class)) {
            return true;
        }

        if ($user->hasRole(Site::pluck('role_id')->unique())) {
            return true;
        }

        return $this->deny('You are not authorized to manage minisites.');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Site $site): Response|bool
    {
        if ($user->can('admin', Site::class)) {
            return true;
        }

        if ($site->role && $user->hasRole($site->role)) {
            return true;
        }

        return $this->deny('You are not authorized to manage this minisites.');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): Response|bool
    {
        return $this->deny('Minisites can only be created by system administrators.');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Site $site): Response|bool
    {
        return $this->view($user, $site);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Site $site): Response|bool
    {
        return $this->deny('Minisites can only be deleted by system administrators.');
    }
}
