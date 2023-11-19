<?php

declare(strict_types=1);

namespace App\Policies\Webcam;

use App\Models\User;
use App\Models\Webcam\Camera;
use Illuminate\Auth\Access\HandlesAuthorization;

class CameraPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any cameras.
     */
    public function viewAny(User $user)
    {
        return $user->hasPermissionTo('camera-admin');
    }

    /**
     * Determine whether the user can view the camera.
     */
    public function view(User $user, Camera $camera)
    {
        return $user->hasPermissionTo('camera-admin');
    }

    /**
     * Determine whether the user can create cameras.
     */
    public function create(User $user)
    {
        return $user->hasPermissionTo('camera-admin');
    }

    /**
     * Determine whether the user can update camera.
     */
    public function update(User $user, Camera $camera)
    {
        return $user->hasPermissionTo('camera-admin');
    }

    /**
     * Determine whether the user can delete camera.
     */
    public function delete(User $user, Camera $camera)
    {
        return $user->hasPermissionTo('camera-admin');
    }
}
