<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;
use App\Models\Webcam;
use Illuminate\Auth\Access\HandlesAuthorization;

class WebcamPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any app models webcams.
     */
    public function viewAny(User $user)
    {
        return $user->hasAnyPermission([
            'plazacam-view',
            'plazacam-update',
        ]);
    }

    /**
     * Determine whether the user can view the app models webcam.
     */
    public function view(User $user, Webcam $webcam)
    {
        return $user->hasAnyPermission([
            'plazacam-view',
            'plazacam-update',
        ]);
    }

    /**
     * Determine whether the user can create app models webcams.
     */
    public function create(User $user)
    {
        return $user->hasPermissionTo('plazacam-update');
    }

    /**
     * Determine whether the user can update the app models webcam.
     */
    public function update(User $user, Webcam $webcam)
    {
        return $user->hasPermissionTo('plazacam-update');
    }

    /**
     * Determine whether the user can delete the app models webcam.
     */
    public function delete(User $user, Webcam $webcam)
    {
        return $user->hasPermissionTo('plazacam-update');
    }
}
