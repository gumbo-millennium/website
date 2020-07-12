<?php

declare(strict_types=1);

namespace Tests;

use App\Models\User;

/**
 * Provides users with various ranks
 */
trait ProvidesUsers
{
    /**
     * Creates a user with the given roles
     * @param array|null $roles
     * @return User
     */
    public function getTemporaryUser(?array $roles = null): User
    {
        $users = factory(User::class, 1)->create();
        $user = $users->first();
        \assert($user instanceof User);

        // Assign roles, if any
        if (!empty($roles)) {
            $user->assignRole($roles);
        }

        // Return user
        return $user;
    }

    /**
     * Returns a user that's only granted guest permissions
     * @return User
     */
    public function getGuestUser(): User
    {
        return $this->getTemporaryUser(['guest']);
    }

    /**
     * Returns a user that's granted member permissions
     * @return User
     */
    public function getMemberUser(): User
    {
        return $this->getTemporaryUser(['member']);
    }

    /**
     * Returns a user that's member of the Activiteiten Commissie
     * @return User
     */
    public function getCommissionUser(): User
    {
        return $this->getTemporaryUser(['member', 'ac']);
    }

    /**
     * Returns a user that's a board member
     * @return User
     */
    public function getBoardUser(): User
    {
        return $this->getTemporaryUser(['member', 'board']);
    }

    /**
     * Returns a user that has super admin rights
     * @return User
     */
    public function getSuperAdminUser(): User
    {
        $user = $this->getTemporaryUser();
        $user->givePermissionTo('super-admin');
        return $user;
    }
}
