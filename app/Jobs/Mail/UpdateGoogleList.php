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
use Illuminate\Support\Facades\Log;
use Stringable;

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
     * Prepare a mutation on the given list.
     */
    public function __construct(string $email, string $name, ?array $aliases, array $members)
    {
        $this->email = $email;
        $this->name = $name;
        $this->aliases = $aliases;
        $this->members = $members;
    }

    /**
     * Updates the mailing list.
     */
    public function handle(MailListHandler $handler): void
    {
        if (! Config::get('services.google.enabled')) {
            return;
        }

        // Get list
        $list = $this->getEmailList($handler);

        // Get change flags
        $mailHandle = Str::beforeLast($list->getEmail(), '@');
        $updateAny = ! in_array($mailHandle, self::NO_CHANGE, true);
        $updateAliases = ! in_array($mailHandle, self::NO_ALIAS_CHANGE, true);
        $updateMembers = ! in_array($mailHandle, self::NO_MEMBER_CHANGE, true);

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

        $hasChanges = ! (empty($list->getChangedAliases()) && empty($list->getChangedEmails()));

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
                'policy' => $perms,
            ]);

            // Commit permissions
            $handler->applyPermissions($list, $perms);
        }

        // Update model
        if (! $hasChanges) {
            return;
        }

        $this->updateModel($this->getEmailList($handler));
    }

    /**
     * Finds or creates list.
     *
     * @return MailList
     */
    public function getEmailList(MailListHandler $handler)
    {
        // Get existing
        $list = $handler->getList($this->email);
        if ($list) {
            Log::debug('Retireved {list} from handler', compact('list'));

            return $list;
        }

        // Make new
        $list = $handler->createList($this->email, $this->name);

        // Log
        Log::info('Created new {list} via handler', compact('list'));

        return $list;
    }

    /**
     * Applies updates to the model.
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
                'role' => $val[1] === MailList::ROLE_ADMIN ? 'admin' : 'user',
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
     * Updates the aliases on the list.
     */
    private function updateAliases(MailList $list): void
    {
        // Speed up search
        $wantedAliases = array_flip($this->aliases);
        $existingAliases = [];

        // Remove extra aliases
        foreach ($list->listAliases() as $alias) {
            // Skip if ok
            if (array_key_exists($alias, $wantedAliases)) {
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
            if (array_key_exists($alias, $existingAliases)) {
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
     * Updates all users on this list, except those who appear to be forwarders.
     *
     * @throws BindingResolutionException
     */
    private function updateMembers(MailList $list): void
    {
        $wantedMembers = array_combine(Arr::pluck($this->members, 0), $this->members);
        $existingMembers = [];

        // Remove extra aliases
        foreach ($list->listEmails() as [$email, $role]) {
            // Add if found
            if (array_key_exists($email, $wantedMembers)) {
                Log::debug('Found required member {email} on list', compact('email'));
                $existingMembers[$email] = $role;

                continue;
            }

            // Don't remove internal members
            $domain = Str::afterLast($email, '@');
            if (in_array($domain, Config::get('services.google.domains'), true)) {
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
        foreach ($this->members as $index => [$email, $role]) {
            // Skip non-emails
            if (! is_string($email) && ! $email instanceof Stringable) {
                Log::warning('Invalid email on index {index}, skipping', [
                    'email' => $email,
                    'role' => $role,
                    'index' => $index,
                ]);

                continue;
            }

            // Ensure email is a string, for the next functions
            $email = (string) $email;

            // Add missing
            if (! array_key_exists($email, $existingMembers)) {
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
                compact('email', 'role') + ['old-row' => $existingMembers[$email]],
            );
            $list->updateEmail($email, $role);
        }
    }
}
