<?php

declare(strict_types=1);

namespace App\Services\Mail;

use App\Contracts\Mail\MailList;
use App\Contracts\Mail\MailListHandler;
use App\Exceptions\Services\GoogleServiceException;
use App\Helpers\Arr;
use App\Helpers\Str;
use App\Services\Mail\Traits\HasGoogleServices;
use App\Services\Mail\Traits\ValidatesEmailRequests;
use Google\Exception as GoogleException;
use Google\Service\Directory\Alias as GroupAlias;
use Google\Service\Directory\Group as Group;
use Google\Service\Directory\Groups;
use Google\Service\Directory\Member as GroupMember;
use Google\Service\Directory\Members as GroupMembers;
use Google\Service\Groupssettings\Groups as GroupSettings;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use LogicException;
use RuntimeException;

/**
 * Handles Google mail classes.
 */
class GoogleMailListService implements MailListHandler
{
    use HasGoogleServices;
    use ValidatesEmailRequests;

    /**
     * Returns all lists.
     *
     * @throws GoogleException
     */
    public function getAllLists(): array
    {
        // Get all domains
        $domains = Config::get('services.google.domains');

        // Get lists per domain
        $lists = [];
        foreach ($domains as $domain) {
            $lists[] = $this->getAllListsForDomain($domain);
        }

        // Collapse
        $lists = Arr::collapse($lists);

        // Convert to e-mail addresses
        $listEmails = [];
        foreach ($lists as $list) {
            $listEmails[] = ShallowGoogleMailList::fromGoogleModel($list, new GroupMembers());
        }

        // Debug
        Log::debug('Email lists ready: {lists}', [
            'lists' => $listEmails,
        ]);

        // Unfold
        return $listEmails;
    }

    public function getList(string $email): ?MailList
    {
        // Get email and domain name
        $domain = Str::after($email, '@');

        // Validate domain
        if (! $this->canProcessList($email)) {
            throw new GoogleServiceException("Domain {$domain} is not available for modification", GoogleServiceException::CODE_DOMAIN_LOCKED);
        }
        // Build query
        $query = [
            'domain' => $domain,
            'query' => sprintf("email='%s'", str_replace("'", "\\'", $email)),
            'maxResults' => 1,
        ];

        // Get group manager JIT
        $groupsManager = $this->getGoogleGroupManager();

        // List groups with this e-mail address
        $result = $this->callGoogleService(
            static fn () => $groupsManager->listGroups($query),
            'get group',
            $email,
        );

        // Check if the result is valid
        if (! $result instanceof Groups || ! $result->valid()) {
            return null;
        }

        // Get first group
        $group = Arr::first($result->getGroups());

        // Null if not found
        if (! $group || ! $group instanceof Group) {
            return null;
        }

        // Fail if null
        if ($group === null) {
            return null;
        }
        // Get members manager JIT
        $memberManager = $this->getGoogleGroupMembersManager();

        // Get members
        $members = $this->callGoogleService(
            static fn () => $memberManager->listMembers($group->getId()),
            'get group members',
            $email,
        );

        // Build and return
        return GoogleMailList::fromGoogleModel($group, $members);
    }

    public function createList(string $email, string $name): MailList
    {
        // Get API
        $groupsManager = $this->getGoogleGroupManager();

        // Ensure email is valid given the name
        if (! $this->validateListNameAgainstEmail($name, $email)) {
            Log::warning('Email address {email} does not meet expectations for {name}', [
                'email' => $email,
                'name' => $name,
            ]);
        }

        // Get email and domain name
        $domain = Str::after($email, '@');

        // Validate domain
        if (! $this->canProcessList($email)) {
            throw new GoogleServiceException("Domain {$domain} is not available for modification", GoogleServiceException::CODE_DOMAIN_LOCKED);
        }

        // Build group model
        $group = new Group([
            'email' => $email,
            'name' => ! empty($name) ? $name : null,
        ]);

        // Insert it
        try {
            $result = $this->callGoogleService(
                static fn () => $groupsManager->insert($group),
                'create group',
                $email,
            );

            if (! $result instanceof Group) {
                throw new GoogleServiceException("Failed to create group {$email}", GoogleServiceException::CODE_GROUP_FAILED);
            }
        } catch (RuntimeException $exception) {
            throw new GoogleServiceException("Failed to create group {$email}", GoogleServiceException::CODE_GROUP_FAILED, $exception);
        }

        // Build new item, without members
        return GoogleMailList::fromGoogleModel($group, new GroupMembers());
    }

    /**
     * Applies permissions to the given list.
     *
     * @throws RuntimeException
     */
    public function applyPermissions(MailList $list, GroupSettings $permissions): void
    {
        // Get manager
        $permissionManager = $this->getGoogleGroupSettingsManager();

        // Apply changes
        try {
            $ok = $this->callGoogleService(
                static fn () => $permissionManager->patch($list->getEmail(), $permissions),
                'update permssions',
                $list->getEmail(),
            );
        } catch (RuntimeException $exception) {
            throw new GoogleServiceException("Failed to set permissions on [{$list->getEmail()}]", GoogleServiceException::CODE_GROUP_PERMISSIONS_FAILED, $exception);
        }
    }

    /**
     * Updates group.
     *
     * @throws GoogleException
     * @throws LogicException
     */
    public function save(MailList $list): void
    {
        // Get services
        $aliasManager = $this->getGoogleGroupAliasManager();
        $memberManager = $this->getGoogleGroupMembersManager();

        // Update aliases
        foreach ($list->getChangedAliases() as $index => [$action, $alias]) {
            // Delete it
            if ($action === MailList::CHANGE_DELETE) {
                try {
                    $this->callGoogleService(
                        static fn () => $aliasManager->delete($list->getServiceId(), $alias),
                        "delete alias {$alias}",
                        $list->getEmail(),
                    );
                } catch (RuntimeException $exception) {
                    report(new GoogleServiceException("Failed to delete alias {$alias} from {$list->getEmail()}", GoogleServiceException::CODE_GROUP_ALIAS_FAILED, $exception));
                }

                continue;
            }

            // Check
            if ($action !== MailList::CHANGE_ADD) {
                throw new LogicException("Invalid action on [{$index}]: [{$action}] [{$alias}]");
            }

            // Make object
            $aliasObj = new GroupAlias([
                'alias' => $alias,
            ]);

            // Add
            try {
                $this->callGoogleService(
                    static fn () => $aliasManager->insert($list->getServiceId(), $aliasObj),
                    "create alias {$alias}",
                    $list->getEmail(),
                );
            } catch (RuntimeException $exception) {
                report(new GoogleServiceException("Failed to create alias {$alias} on {$list->getEmail()}", GoogleServiceException::CODE_GROUP_ALIAS_FAILED, $exception));
            }
        }

        // Update members
        foreach ($list->getChangedEmails() as $index => [$action, $email, $role]) {
            // Delete it
            if ($action === MailList::CHANGE_DELETE) {
                try {
                    $this->callGoogleService(
                        static fn () => $memberManager->delete($list->getServiceId(), $email),
                        "delete member {$email}",
                        $list->getEmail(),
                    );
                } catch (RuntimeException $exception) {
                    report(new GoogleServiceException("Failed to delete member {$email} from {$list->getEmail()}", GoogleServiceException::CODE_GROUP_MEMBER_FAILED, $exception));
                }

                continue;
            }

            // Check
            if ($action !== MailList::CHANGE_ADD && $action !== MailList::CHANGE_UPDATE) {
                throw new LogicException("Invalid action on [{$index}]: [{$action}] [{$alias}]");
            }

            // Prep object
            $memberObj = new GroupMember([
                'email' => $email,
                'role' => $role,
            ]);

            // Patch if update
            if ($action === MailList::CHANGE_UPDATE) {
                try {
                    $this->callGoogleService(
                        static fn () => $memberManager->patch($list->getServiceId(), $email, $memberObj),
                        "update member {$email}",
                        $list->getEmail(),
                    );
                } catch (RuntimeException $exception) {
                    report(new GoogleServiceException("Failed to update member {$email} on {$list->getEmail()}", GoogleServiceException::CODE_GROUP_MEMBER_FAILED, $exception));
                }

                continue;
            }

            // Insert new member
            try {
                $this->callGoogleService(
                    static fn () => $memberManager->insert($list->getServiceId(), $memberObj),
                    "add member {$email}",
                    $list->getEmail(),
                );
            } catch (RuntimeException $exception) {
                report(new GoogleServiceException("Failed to add member {$email} to {$list->getEmail()}", GoogleServiceException::CODE_GROUP_MEMBER_FAILED, $exception));
            }
        }
    }

    /**
     * Deletes the given maillist.
     */
    public function delete(MailList $list): void
    {
        // Get manager
        $groupManager = $this->getGoogleGroupManager();

        // Delete group
        $res = $this->callGoogleService(
            static fn () => $groupManager->delete($list->getServiceId()),
            'delete',
            $list->getEmail(),
        );

        // Check if failed
        if ($res === null) {
            throw new GoogleServiceException("Failed to delete group {$list->getEmail()}", GoogleServiceException::CODE_GROUP_FAILED);
        }
    }

    /**
     * Gets all mailing lists for each domain.
     *
     * @throws GoogleException
     */
    protected function getAllListsForDomain(string $domain): array
    {
        // Get mananger
        $groupsManager = $this->getGoogleGroupManager();

        // List groups, recursing pages
        $groups = [];
        $pageToken = null;
        do {
            // Prep args
            $args = array_filter(compact('domain', 'pageToken'));

            // Get groups
            $groupItem = $this->callGoogleService(
                static fn () => $groupsManager->listGroups($args),
                'list groups',
                $domain,
            );

            // Add each item from result set
            $groups[] = $groupItem->getGroups();

            // Get next token
            $pageToken = $groupItem->getNextPageToken();

            // Loop while we have results and there's a next page token
        } while ($groupItem->count() > 0 && ! empty($pageToken));

        // Join
        return Arr::collapse($groups);
    }
}
