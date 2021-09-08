<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;
use App\Models\WebcamUpdate;
use Illuminate\Auth\Access\HandlesAuthorization;

class WebcamUpdatePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any app models webcam updates.
     */
    public function viewAny(User $user)
    {
        return $user->hasPermissionTo('plazacam-update');
    }

    /**
     * Determine whether the user can view the app models webcam update.
     */
    public function view(User $user, WebcamUpdate $webcamUpdate)
    {
        return $user->hasPermissionTo('plazacam-update');
    }

    /**
     * Determine whether the user can create app models webcam updates.
     */
    public function create(User $user)
    {
        return false;
    }

    /**
     * Determine whether the user can update the app models webcam update.
     */
    public function update(User $user, WebcamUpdate $webcamUpdate)
    {
        return false;
    }

    /**
     * Determine whether the user can delete the app models webcam update.
     */
    public function delete(User $user, WebcamUpdate $webcamUpdate)
    {
        return $user->hasPermissionTo('plazacam-update');
    }
}
