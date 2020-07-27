<?php

declare(strict_types=1);

namespace App\Jobs\Mail;

use App\Contracts\Mail\MailList;
use App\Contracts\Mail\MailListHandler;
use App\Helpers\Arr;
use App\Helpers\Str;
use App\Models\EmailList;
use App\Services\Mail\GoogleMailListService;
use App\Services\Mail\GooglePermissionFactory;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Config;
use Log;

class UpdateGoogleList implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    private const NO_CHANGE = [];
    private const NO_ALIAS_CHANGE = [];
    private const NO_MEMBER_CHANGE = [
        // Board is all internally linked
        'bestuur',

        // Some lists are not directly linked to members, so don't change these
        'aliquando',
        'm-power',
        'proximus',
    ];

    protected string $email;
    protected string $name;
    protected ?array $aliases;
    protected array $members;

    /**
     * Prepare a mutation on the given list
     * @param string $email
     * @param string $name
     * @param null|array $aliases
     * @param array $members
     */
    public function __construct(string $email, string $name, ?array $aliases, array $members)
    {
        $this->email = $email;
        $this->name = $name;
        $this->aliases = $aliases;
        $this->members = $members;
    }

    /**
     * Updates the mailing list
     * @param MailListHandler $handler
     * @return void
     */
    public function handle(MailListHandler $handler): void
    {
        // Get existing
        $list = $handler->getList($this->email);

        // If none was found, only create it
        if (!$list) {
            // Make new
            $list = $handler->createList($this->email, $this->name);

            // Log
            Log::info("Created new {list} via handler, stopping job", compact('list'));

            // Stop job, allow Google to process for some time
            return;
        }


        // Get change flags
        $mailHandle = Str::beforeLast($list->getEmail(), '@');
        $updateAny = !\in_array($mailHandle, self::NO_CHANGE);
        $updateAliases = !\in_array($mailHandle, self::NO_ALIAS_CHANGE);
        $updateMembers = !\in_array($mailHandle, self::NO_MEMBER_CHANGE);

        // Update model
        $this->updateModel($list);

        // Update aliases
        if ($this->aliases !== null && $updateAny && $updateAliases) {
            $this->updateAliases($list);
        }

        // Update members
        if ($updateAny && $updateMembers) {
            $this->updateMembers($list);
        }

        $hasChanges = !(empty($list->getChangedAliases()) && empty($list->getChangedEmails()));

        // Commit changes
        if ($hasChanges) {
            $handler->save($list);
        }

        // Update permissions
        if ($handler instanceof GoogleMailListService) {
            // Build permissions
            $perms = GooglePermissionFactory::make()
                ->build();

            // Log changes
            Log::info('Applying new lpolicy to {list}', [
                'list' => $list,
                'policy' => $perms
            ]);

            // Commit permissions
            $handler->applyPermissions($list, $perms);
        }

        // Update model
        if ($hasChanges) {
            $this->updateModel($handler->getList($this->email));
        }
    }

    /**
     * Applies updates to the model
     * @param MailList $list
     * @return void
     */
    public function updateModel(MailList $list): void
    {
        // Get model
        $model = EmailList::firstOrNew(['email' => $list->getEmail()]);

        // Log
        Log::debug('Updating {model} to match {list}', compact('model', 'list'));

        // Build aliases
        $aliases = $list->listAliases();
        sort($aliases);

        // Build members
        $members = collect($list->listEmails())
            ->map(static fn ($val) => [
                'email' => $val[0],
                'role' => $val[1] === MailList::ROLE_ADMIN ? 'admin' : 'user'
            ])
            ->toArray();

        // Assign
        $model->service_id = $list->getServiceId();
        $model->name = $this->name;
        $model->aliases = $aliases;
        $model->members = $members;

        // Log
        Log::info('Updated {model} to match {list}', compact('model', 'list'));

        // Save
        $model->save();
    }

    /**
     * Updates the aliases on the list
     * @param MailList $list
     * @return void
     */
    private function updateAliases(MailList $list): void
    {
        // Speed up search
            $wantedAliases = \array_flip($this->aliases);
            $existingAliases = [];

            // Remove extra aliases
        foreach ($list->listAliases() as $alias) {
            // Skip if ok
            if (\array_key_exists($alias, $wantedAliases)) {
                Log::debug('Found required alias {alias} on list', compact('alias'));
                $existingAliases[$alias] = true;
                continue;
            }

            // Remove if excessive
            Log::notice('Flagged alias {alias} for removal', compact('alias'));
            $list->deleteAlias($alias);
        }

            // Add missing aliases
        foreach ($this->aliases as $alias) {
            // Skip if exists
            if (\array_key_exists($alias, $existingAliases)) {
                Log::debug('Already found alias {alias} on list', compact('alias'));
                echo "Found existing {$alias}\n";
                continue;
            }

            // Add missing
            Log::notice('Flagged alias {alias} for addition', compact('alias'));
            $list->addAlias($alias);
        }
    }

    /**
     * Updates all users on this list, except those who appear to be forwarders
     * @param MailList $list
     * @return void
     * @throws BindingResolutionException
     */
    private function updateMembers(MailList $list): void
    {
        $wantedMembers = \array_combine(Arr::pluck($this->members, 0), $this->members);
        $existingMembers = [];
        $localDomains = Config::get('services.google.domains');
        $localSubdomains = \array_map(static fn ($row) => ".{$row}", $localDomains);

        // Remove extra aliases
        foreach ($list->listEmails() as [$email, $role]) {
            // Add if found
            if (\array_key_exists($email, $wantedMembers)) {
                Log::debug('Found required member {email} on list', compact('email'));
                $existingMembers[$email] = $role;
                continue;
            }

            // Don't remove internal members
            $domain = Str::afterLast($email, '@');
            if (\in_array($domain, $localDomains) || Str::endsWith($domain, $localSubdomains)) {
                Log::info('Found internal member {email}, whitelisting it', compact('email'));
                $existingMembers[$email] = $role;
                continue;
            }

            // Remove if excess
            Log::notice('Flagging member {email} for removal', compact('email'));
            $list->removeEmail($email);
            continue;
        }

        // Add missing and invalid
        foreach ($this->members as [$email, $role]) {
            // Add missing
            if (!\array_key_exists($email, $existingMembers)) {
                Log::notice('Flagging member {email} as {role}', compact('email', 'role'));
                $list->addEmail($email, $role);
                continue;
            }

            // Continue if up-to-date
            if ($existingMembers[$email] === $role) {
                Log::debug('Checked {email}, it\'s up-to-date', compact('email'));
                continue;
            }

            // Update role
            Log::info(
                'Flagging member {email} to change from {old-role} to {role}',
                compact('email', 'role') + ['old-row' => $existingMembers[$email]]
            );
            $list->updateEmail($email, $role);
        }
    }
}
