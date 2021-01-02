<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Contracts\ConscriboService;
use App\Helpers\Arr;
use App\Jobs\Stripe\CustomerUpdateJob;
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
        'restricted',
    ];

    /**
     * The user we're updating
     *
     * @var User
     */
    protected User $user;

    /**
     * Create a new job instance.
     *
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
     *
     * @return void
     */
    public function handle(ConscriboService $service)
    {
        // Get user
        $user = $this->user;

        // Skip if user e-mail is not verified
        if (!$user->hasVerifiedEmail()) {
            logger()->info('A role update job was started, but this user is not verified');
            return;
        }

        // Get user from Conscribo
        $accountingUser = $this->getConscriboUser($service, $user);

        if (!$accountingUser) {
            // Log it
            logger()->notice('No user in Conscribo this e-mail address', compact('user'));

            // Remove Conscribo-provided data
            $user->conscribo_id = null;
            $user->address = null;
            $user->phone = null;

            // Save it
            $user->save();

            // Trigger update
            $this->maybeIssueStripeUpdate($user);

            // End job
            return;
        }

        logger()->debug('Got user from Conscribo');

        // Start transaction
        DB::beginTransaction();

        // Update credentials
        $this->updateUserDetails($user, $accountingUser);

        // Trigger update
        $this->maybeIssueStripeUpdate($user);

        // Assign member role
        $this->assignMemberRole($user, $accountingUser);

        // Assign other roles
        $this->assignRoles($user, $accountingUser);

        // Apply changes
        DB::commit();
    }

    /**
     * Assign the member role
     *
     * @param User $user
     * @param array $accountingUser
     * @return void
     * @throws InvalidArgumentException
     */
    public function assignMemberRole(User $user, array $accountingUser): void
    {
        // Check dates
        $memberStarted = $accountingUser['startdatum_lid'] !== null && $accountingUser['startdatum_lid'] < now();
        $memberEnded = $accountingUser['einddatum_lid'] !== null && $accountingUser['einddatum_lid'] < now();

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

        // No change
        logger()->info("User's member-state is already up-to-date.", [
            'user' => $user,
            'checks' => [
                'is-member' => $isMember,
                'member-started' => $memberStarted,
                'member-ended' => $memberEnded,
            ],
        ]);
    }

    /**
     * Issues an update on Stripe if any visible data was changed
     *
     * @param User $user
     * @return void
     */
    private function maybeIssueStripeUpdate(User $user): void
    {
        // Check for changes
        if (empty($user->wasChanged(['first_name', 'insert', 'last_name', 'phone', 'address']))) {
            return;
        }

        // Issue a Stripe customer update
        CustomerUpdateJob::dispatch($user);
    }

    /**
     * Returns user information from Conscribo
     *
     * @param ConscriboService $service
     * @param string $email
     * @return array|null
     */
    private function getConscriboUser(ConscriboService $service, User $dbUser): ?array
    {
        $user = null;
        $groups = null;

        $query = ['email' => $dbUser->email];
        if (!empty($dbUser->conscribo_id)) {
            $query = ['code' => $dbUser->conscribo_id];
        }

        try {
            // Get user info from API
            $user = $service->getResource('user', $query, [
                'code',
                'selector',

                // Names
                'naam',
                'voornaam',
                'tussenvoegsel',

                // Contact info
                'email',
                'telefoonnummer',

                // Address
                'straat',
                'huisnr',
                'huisnr_toev',
                'postcode',
                'plaats',

                // Membership
                'startdatum_lid',
                'einddatum_lid',
            ])->first();

            if (!$user) {
                return null;
            }

            // Print result
            logger()->info('Recieved {user} for {query}', [
                'user' => [
                    'code' => Arr::get($user, 'code'),
                    'name' => Arr::get($user, 'name'),
                ],
                'query' => $query,
            ]);
        } catch (HttpExceptionInterface $exception) {
            report(new RuntimeException('Failed to get user from API', 0, $exception));
            return null;
        }

        try {
            // Get group info from API
            $groups = $service->getResource('role', [
                ['leden', '~', $user['selector']],
            ], ['code', 'naam', 'leden'], ['limit' => 100]);

            // Print result
            logger()->info('Recieved {groups} for user', compact('groups'));
        } catch (HttpExceptionInterface $exception) {
            report(new RuntimeException('Failed to get groups from API', 0, $exception));
            return null;
        }

        // Return user with groups as element
        return array_merge($user, [
            'groups' => $groups->pluck('naam', 'code')->toArray(),
        ]);
    }

    private function updateUserDetails(User $user, array $accountingUser): void
    {
        $notEmptyGet = static function ($key) use ($accountingUser) {
            $val = trim((string) Arr::get($accountingUser, $key));
            return !empty($val) ? $val : null;
        };

        // Store ID
        $user->conscribo_id = $accountingUser['code'];

        // Update name
        $user->first_name = $notEmptyGet('voornaam');
        $user->insert = $notEmptyGet('tussenvoegsel');
        $user->last_name = $notEmptyGet('naam');

        // Update address
        $firstLine = collect(['straat', 'huisnr', 'huisnr_toev'])
            ->map(static fn ($line) => $notEmptyGet($line))
            ->reject(static fn ($value) => empty($value))
            ->implode(' ');

        $newAddress = [
            'line1' => $firstLine,
            'line2' => null,
            'postal_code' => $notEmptyGet('postcode'),
            'city' =>  $notEmptyGet('plaats'),
            'country' => 'nl',
        ];

        if ($user->address !== $newAddress) {
            $user->address = $newAddress;
        }

        // Update phone number
        $user->phone = $notEmptyGet('telefoonnummer');

        // Save changes
        $user->save();
    }

    /**
     * Assigns roles to the user, as a transaction
     *
     * @param User $user
     * @param array $accountingUser
     * @return void
     * @throws \InvalidArgumentException
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
}
