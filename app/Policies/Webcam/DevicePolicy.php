<?php

declare(strict_types=1);

namespace App\Policies\Webcam;

use App\Models\User;
use App\Models\Webcam\Device;
use Illuminate\Auth\Access\HandlesAuthorization;

class DevicePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any devices.
     */
    public function viewAny(User $user)
    {
        return $user->hasAnyPermission([
            'device-write',
            'device-admin',
        ]);
    }

    /**
     * Determine whether the user can view the device.
     */
    public function view(User $user, Device $device)
    {
        return $device->owner->is($user) || $user->hasPermissionTo('device-admin');
    }

    /**
     * Determine whether the user can create devices.
     */
    public function create(User $user)
    {
        return $user->hasAnyPermission([
            'device-write',
            'device-admin',
        ]);
    }

    /**
     * Determine whether the user can update device.
     */
    public function update(User $user, Device $device)
    {
        return $device->owner->is($user) || $user->hasPermissionTo('device-admin');
    }

    /**
     * Determine whether the user can delete device.
     */
    public function delete(User $user, Device $device)
    {
        return $device->owner->is($user) || $user->hasPermissionTo('device-admin');
    }
}
