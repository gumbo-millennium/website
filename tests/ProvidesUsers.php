<?php

declare(strict_types=1);

namespace Tests;

use App\Models\User;

/**
 * Provides users with various ranks.
 */
trait ProvidesUsers
{
    /**
     * Creates a user with the given roles.
     */
    public function getTemporaryUser(?array $roles = null): User
    {
        /** @var User $user */
        return tap(
            User::factory()->create(),
            fn (User $user) => ! empty($roles) && $user->assignRole(...$roles),
        );
    }

    /**
     * Returns a user that's only granted guest permissions.
     */
    public function getGuestUser(): User
    {
        return $this->getTemporaryUser(['guest']);
    }

    /**
     * Returns a user that's granted member permissions.
     */
    public function getMemberUser(): User
    {
        return $this->getTemporaryUser(['member']);
    }

    /**
     * Returns a user that's member of the Activiteiten Commissie.
     */
    public function getCommissionUser(): User
    {
        return $this->getTemporaryUser(['member', 'ac']);
    }

    /**
     * Returns a user that's a board member.
     */
    public function getBoardUser(): User
    {
        return $this->getTemporaryUser(['member', 'board']);
    }

    /**
     * Returns a user that has super admin rights.
     */
    public function getSuperAdminUser(): User
    {
        return tap(
            $this->getTemporaryUser(),
            fn (User $user) => $user->givePermissionTo('super-admin'),
        );
    }
}
