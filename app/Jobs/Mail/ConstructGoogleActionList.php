<?php

declare(strict_types=1);

namespace App\Jobs\Mail;

use App\Contracts\ConscriboService;
use App\Contracts\Mail\MailList;
use App\Helpers\Arr;
use App\Helpers\Str;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ConstructGoogleActionList implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    private const EMAIL_REMAP = [
        'lhw@gumbo-millennium.nl' => 'lhc@gumbo-millennium.nl',
    ];

    public static function test()
    {
        $inst = new self();
        \app()->call([$inst, 'handle']);
    }

    /**
     * Execute the job.
     * @return void
     */
    public function handle(ConscriboService $conscribo)
    {
        // Get all roles, removing those without email
        $roles = $conscribo
            ->getResource('role', [], ['code', 'naam', 'leden', 'e_mailadres'])
            ->reject(static fn ($row) => empty($row['e_mailadres']));

        Log::info("Recieved roles from Conscribo", compact('roles'));

        // Map "leden" to an array
        // 1) Get the 'leden' properties and split it on comma's followed by a digit (values are "1: user, 2: user")
        // 3) Sort by member ID by casting to a number
        $roles = $roles->map(static function (&$role) {
            $memberList = preg_split('/\,\s*(?=\d+\:)/', $role['leden'] ?? '');
            $role['leden'] = collect($memberList)
                ->each('trim')
                ->filter()
                ->sort(static fn ($a, $b) => intval($a) <=> intval($b))
                ->toArray();
            return $role;
        });

        // Get all unique users on all roles
        // 1) Get the 'leden' array and collapse the nested array
        // 2) Remove the duplicates
        // 3) Cast to array
        $userIds = $roles
            ->pluck('leden')
            ->collapse()
            ->flip()
            ->keys()
            ->toArray();

        // Log count
        Log::debug("Will look for {member-count} members", [
            'member-count' => count($userIds)
        ]);

        // Get users from Conscribo
        $userResource = $conscribo->getResource(
            'user',
            [['selector', '~', $userIds]],
            ['selector', 'email']
        );

        // Log count
        Log::debug("Received {member-count} members from Conscribo", [
            'member-count' => count($userResource)
        ]);

        // Map emails by selector, remove empties and lowercase email
        $emails = $userResource
            ->pluck('email', 'selector')
            ->filter()
            ->map(static fn ($val) => Str::lower(trim($val)));

        // Log count
        Log::info("After filter, got left with {email-count} email addresses from Conscribo", [
            'email-count' => count($emails),
            'query-count' => count($userResource),
            'search-count' => count($userIds)
        ]);

        // Map new models
        $jobList = collect();
        foreach ($roles as $role) {
            // Build member list
            $jobMembers = $emails
                ->only($role['leden'])
                ->values()
                ->map(static fn ($val) => [$val, MailList::ROLE_NORMAL])
                ->toArray();

            // Build job
            $job = [
                'email' => Str::lower($role['e_mailadres']),
                'name' => $role['naam'],
                'aliases' => null,
                'members' => $jobMembers
            ];

            // Allow for alias changing
            if (!empty(self::EMAIL_REMAP[$job['email']])) {
                Log::debug('Remapping job {job} to use {new-email} instead of {old-email}', [
                    'job' => Arr::except($job, 'members'),
                    'old-email' => $job['email'],
                    'new-email' => self::EMAIL_REMAP[$job['email']]
                ]);
                $job['email'] = self::EMAIL_REMAP[$job['email']];
            }

            Log::info('Created job {job}, adding to list', [
                'job' => Arr::except($job, 'members')
            ]);

            // Add job
            $jobList->push($job);
        }

        // Get safe domains
        $validDomains = \config('services.google.domains', []);

        // Start a job for each email
        foreach ($jobList as $job) {
            $domain = Str::afterLast($job['email'], '@');
            if (!\in_array($domain, $validDomains)) {
                Log::warning('Tried to start job for {email}, which isn\'t in the safe domain list', [
                    'email' => $job['email'],
                    'safe-domains' => $validDomains
                ]);
                continue;
            }

            Log::info("Dispatching new Update job for {email}", [
                'email' => $job['email'],
                'job' => array_merge($job, [
                    'members' => sprintf('[REDACTED %d EMAILS]', count($job['members']))
                ])
            ]);

            UpdateGoogleList::dispatch(
                $job['email'],
                $job['name'],
                $job['aliases'],
                $job['members']
            );
        }
    }
}
