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
     * All users created during this request.
     *
     * @var array<User>
     */
    private $createdUsers = [];

    /**
     * Delete users after the class is done testing.
     *
     * @after
     */
    public function tearDownUsers(): void
    {
        // Edge case when queries are being monitored
        $this->ensureApplicationExists();

        // Delete users afterwards
        foreach ($this->createdUsers as $user) {
            $user->delete();
        }
    }

    /**
     * Creates a user with the given roles.
     */
    public function getTemporaryUser(?array $roles = null): User
    {
        $users = factory(User::class, 1)->create();
        $user = $users->first();
        \assert($user instanceof User);

        // Assign roles, if any
        if (! empty($roles)) {
            $user->assignRole($roles);
        }

        // Add to deletion queue
        $this->createdUsers[] = $user;

        // Return user
        return $user;
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
        $user = $this->getTemporaryUser();
        $user->givePermissionTo('super-admin');

        return $user;
    }
}
