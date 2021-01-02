<?php

declare(strict_types=1);

namespace App\Console\Commands\Gumbo;

use App\Console\Commands\Traits\FindsUserTrait;
use App\Contracts\ConscriboServiceContract;
use App\Helpers\Str;
use App\Models\Role;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Adds a role via CLI
 *
 * @author Roelof Roos <github@roelof.io>
 * @license MPL-2.0
 */
class UpdateGroups extends Command
{
    use FindsUserTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'gumbo:update-groups
                            {--missing : Also create missing groups}
                            {--prune : Remove groups not whitelisted}
                            {--N|dry-run : Only show changes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = <<<'DESC'
    Synchronises groups from Conscribo, optionally purging old ones and creating missing ones
    DESC;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle(ConscriboServiceContract $service)
    {
        // Get all roles
        $systemRoles = Role::get();
        $adminRoles = $service->getResource('role', [], ['code', 'naam', 'leden', 'e_mailadres']);
        $this->matchExistingGroups($systemRoles, $adminRoles);
        $this->updateGroupTitles($systemRoles, $adminRoles);

        // Add missing groups
        if ($this->option('missing')) {
            $this->createMissingGroups($systemRoles, $adminRoles);
        }

        // Prune excess groups
        if (!$this->option('prune')) {
            return;
        }

        $this->pruneGroups($systemRoles, $adminRoles);
    }

    /**
     * Converts a value to a slugged value we can compare
     *
     * @param string $value
     * @return string
     */
    private function slugged(string $value): string
    {
        return Str::slug($value, '');
    }

    /**
     * Returns a group name
     *
     * @return string
     */
    private function named(array $role): string
    {
        return Str::beforeLast($role['e_mailadres'], '@');
    }

    /**
     * Match existing groups that don't have a conscribo_id yet.
     *
     * @param Collection $systemRoles
     * @param Collection $adminRoles
     * @return void
     */
    private function matchExistingGroups(Collection $systemRoles, Collection $adminRoles): void
    {

        // Find roles without a Conscribo ID
        foreach ($systemRoles->whereNull('conscribo_id') as $role) {
            // Skip if the role is required
            if (\in_array($role->name, Role::REQUIRED_GROUPS)) {
                continue;
            }

            // Check all roles from Concribo for the role we're looking for
            foreach ($adminRoles as $adminRole) {
                if (
                    $this->named($adminRole) !== $this->slugged($role->name) &&
                    $this->slugged($adminRole['naam']) !== $this->slugged($role->name) &&
                    $this->slugged($adminRole['naam']) !== $this->slugged($role->title)
                ) {
                    continue;
                }

                // Assign and save
                $role->conscribo_id = $adminRole['code'];
                $role->save(['conscribo_id']);

                // Log it
                $this->line(sprintf(
                    'Associated role <info>%s</> (<comment>%s</>) with <info>%s</> (<comment>%s</>)',
                    $role->title,
                    $role->name,
                    $adminRole['naam'],
                    $role->conscribo_id
                ), null, OutputInterface::VERBOSITY_VERBOSE);

                // Continue to next item
                continue 2;
            }

            // Report failure
            $this->line(sprintf(
                'Could not find suitable role for <error>%s</> (<comment>%s</>)!',
                $role->title,
                $role->name
            ));
        }
    }

    /**
     * Update the titles of groups that are linked to a Conscribo group
     *
     * @param Collection $systemRoles
     * @param Collection $adminRoles
     * @return void
     */
    private function updateGroupTitles(Collection $systemRoles, Collection $adminRoles): void
    {
        // Only show a 'should prune' notice once.
        $shownPrune = false;

        // Update titles of existing commissions
        foreach ($systemRoles->whereNotNull('conscribo_id') as $role) {
            $adminRole = $adminRoles->where('code', $role->conscribo_id)->first();

            // If a role no longer exists, warn and ignore
            if ($adminRole === null) {
                if (!$shownPrune) {
                    $this->line('<question>Roles exist that are no longer in Conscribo, consider pruning.</>');
                    $shownPrune = true;
                }
                continue;
            }

            // Skip if the title already matches
            if ($role->title === $adminRole['naam']) {
                continue;
            }

            // Update the title and save the old one for logging
            $oldTitle = $role->title;
            $role->title = $adminRole['naam'];
            $role->save(['title']);

            // Report result
            $this->line(sprintf(
                'Changed name of <comment>%s</> from <info>%s</> to <info>%s</>',
                $role->name,
                $oldTitle,
                $role->title,
            ), null, OutputInterface::VERBOSITY_VERBOSE);
        }
    }

    /**
     * Create roles for all Conscribo groups that don't yet exist.
     *
     * @param Collection $systemRoles
     * @param Collection $adminRoles
     * @return void
     * @throws RoleAlreadyExists
     */
    private function createMissingGroups(Collection $systemRoles, Collection $adminRoles): void
    {
        $missingRoles = $adminRoles->whereNotIn('code', $systemRoles->pluck('conscribo_id'));
        $created = 0;
        foreach ($missingRoles as $adminRole) {
            $groupName = Str::beforeLast($adminRole['e_mailadres'], '@');

            // In case there's no email address
            if (empty($groupName)) {
                $groupName = $adminRole['naam'];
            }

            // Create the role
            $role = Role::create([
                'name' => Str::slug($groupName), // always slug in case of anomalies
                'title' => $adminRole['naam'],
                'conscribo_id' => $adminRole['code'],
            ]);

            // Raise count and report
            $created++;

            $this->line(sprintf(
                'Created <info>%s</> (<comment>%s</>) from <info>%s</> (<comment>%s</>).',
                $role->title,
                $role->name,
                $role->title,
                $role->conscribo_id,
            ), null, OutputInterface::VERBOSITY_VERBOSE);
        }

        // Log
        $this->info("Created <comment>{$created}</> groups from Conscribo.");
    }

    /**
     * Remove groups whose code is no longer in Conscribo and which aren't essential
     *
     * @param Collection $systemRoles
     * @param Collection $adminRoles
     * @return void
     */
    private function pruneGroups(Collection $systemRoles, Collection $adminRoles): void
    {
        // Only match roles that have a Conscribo ID but no longer exist.
        // never remove required roles
        $excessRoles = $systemRoles
            ->whereNotIn('conscribo_id', $adminRoles->pluck('code'))
            ->whereNotNull('conscribo_id')
            ->whereNotIn('name', Role::REQUIRED_GROUPS);

        $deleted = 0;
        foreach ($excessRoles as $role) {
            // Delete the role
            $role->delete();

            // Increment and log
            $deleted++;
            $this->line(sprintf(
                'Deleted <info>%s</> (<comment>%s</>).',
                $role->title,
                $role->name,
            ), null, OutputInterface::VERBOSITY_VERBOSE);
        }

        // Show the result
        $this->info("Deleted <comment>{$deleted}</> groups from database.");
    }
}
