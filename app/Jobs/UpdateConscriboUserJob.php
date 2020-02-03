<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Contracts\ConscriboServiceContract;
use App\Models\Role;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

/**
 * Updates the user's roles by asking the Conscribo API
 */
class UpdateConscriboUserJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    private const RESERVED_ROLE = [
        'member',
        'verified',
        'restricted'
    ];

    /**
     * The user we're updating
     * @var User
     */
    protected User $user;

    /**
     * Create a new job instance.
     * @param User $user
     * @return void
     */
    public function __construct(User $user)
    {
        //
        $this->user = $user->withoutRelations();
    }

    /**
     * Execute the job.
     * @return void
     */
    public function handle(ConscriboServiceContract $service)
    {
        // Get user
        $user = $this->user;

        // Skip if user e-mail is not verified
        if (!$user->hasVerifiedEmail()) {
            logger()->info('A role update job was started, but this user is not verified');
            return;
        }

        // Get user from Conscribo
        $accountingUser = $this->getConscriboUser($service, $user->email);

        if (!$accountingUser) {
            logger()->notice('No user in Conscribo this e-mail address', compact('user'));
            return;
        }

        // Start transaction
        DB::beginTransaction();

        // Assign member role
        $this->assignMemberRole($user, $accountingUser);

        // Assign other roles
        $this->assignRoles($user, $accountingUser);

        // Apply changes
        DB::commit();
    }


    /**
     * Returns user information from Conscribo
     * @param ConscriboServiceContract $service
     * @param string $email
     * @return null|array
     */
    private function getConscriboUser(ConscriboServiceContract $service, string $email): ?array
    {
        $user = null;
        $groups = null;

        try {
            // Get user info from API
            $user = $service->getResource('user', [
                'email' => $email
            ], [
                'code',
                'voornaam',
                'tussenvoegsel',
                'email',
                'naam',
                'startdatum_lid',
                'einddatum_lid',
            ])->first();

            if (!$user) {
                return null;
            }

            // Print result
            logger()->info('Recieved {user} for {email}', compact('user', 'email'));
        } catch (HttpExceptionInterface $exception) {
            report(new RuntimeException('Failed to get user from API', 0, $exception));
            return null;
        }

        try {
            // Get group info from API
            $groups = $service->getResource('role', [
                ['leden', '~', $user['code']]
            ], ['code', 'naam', 'leden'], ['limit' => 100]);

            // Make sure the user is actually a part of this group
            // phpcs:ignore SlevomatCodingStandard.Functions.RequireArrowFunction.RequiredArrowFunction
            $groups = $groups->filter(static function ($item) use ($user) {
                return collect(explode(',', $item['leden']))
                    ->map(static fn ($row) => intval(explode(':', trim($row), 2)[0]), 10)
                    ->contains((string) $user['code']);
            });

            // Print result
            logger()->info('Recieved {groups} for user', compact('groups'));
        } catch (HttpExceptionInterface $exception) {
            report(new RuntimeException('Failed to get groups from API', 0, $exception));
            return null;
        }

        // Return user with groups as element
        return array_merge($user, [
            'groups' => $groups->pluck('naam', 'code')->toArray()
        ]);
    }

    /**
     * Assigns roles to the user, as a transaction
     * @param User $user
     * @param array $accountingUser
     * @return void
     * @throws \InvalidArgumentException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    private function assignRoles(User $user, array $accountingUser): void
    {
        // Update roles
        $roles = Role::whereIn('title', $accountingUser['groups'])->get();
        $allowedRoles = $roles->pluck('name')->toArray();

        // Assign missing roles
        $skippedRoles = [];
        $addedRoles = [];
        $presentRoles = [];
        $removedRoles = [];

        // Check current roles
        foreach ($user->roles as $role) {
            // Don't mutate reserved roles
            if (in_array($role->name, self::RESERVED_ROLE)) {
                $skippedRoles[] = $role->name;
                continue;
            }

            // Check if in list
            if (!in_array($role->name, $allowedRoles)) {
                $removedRoles[] = $role->name;
                logger()->info("Removing role [{role}] from user.", compact('role', 'user'));
                $user->removeRole($role);
                continue;
            }

            // Add valid role to whitelist
            $presentRoles[] = $role->name;
        }

        foreach ($roles as $role) {
            // Already existing roles are skipped
            if (in_array($role->name, $presentRoles)) {
                continue;
            }

            // Add role
            logger()->info("Adding role [{role}] from user.", compact('role', 'user'));
            $user->assignRole($role);

            // Add new role to list
            $addedRoles[] = $role->name;
        }
    }

    /**
     * Assign the member role
     * @param User $user
     * @param array $accountingUser
     * @return void
     * @throws InvalidArgumentException
     */
    public function assignMemberRole(User $user, array $accountingUser): void
    {
        // Check dates
        $memberStarted = $accountingUser['startdatum_lid'] !== null && $accountingUser['startdatum_lid'] < now();
        $memberEnded = $accountingUser['einddatum_lid'] !== null || $accountingUser['einddatum_lid'] < now();

        // Check member state
        $isMember = $memberStarted && !$memberEnded;

        // Remove member if member without permission
        if ($isMember !== $user->is_member && !$isMember) {
            logger()->info("Removing member role from user.", compact('user'));
            $user->removeRole('member');
            return;
        }

        // Add member if not member yet
        if ($isMember !== $user->is_member && $isMember) {
            logger()->info("Adding member role to user.", compact('user'));
            $user->assignRole('member');
        }
    }
}
