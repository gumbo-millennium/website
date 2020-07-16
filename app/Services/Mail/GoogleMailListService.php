<?php

declare(strict_types=1);

namespace App\Services\Mail;

use App\Contracts\Mail\MailList;
use App\Contracts\Mail\MailListHandler;
use App\Helpers\Arr;
use App\Helpers\Str;
use App\Services\Mail\Traits\HasGoogleServices;
use Google_Service_Directory_Alias as GroupAlias;
use Google_Service_Directory_Group as Group;
use Google_Service_Directory_Member as GroupMember;
use Google_Service_Directory_Members as GroupMembers;
use Google_Service_Groupssettings_Groups as GroupSettings;
use LogicException;
use RuntimeException;

/**
 * Handles Google mail classes
 */
class GoogleMailListService implements MailListHandler
{
    use HasGoogleServices;

    /**
     * Returns all lists
     * @return array
     * @throws Google_Exception
     */
    public function getAllLists(): array
    {
        // Get all domains
        $domains = \config('services.google.domains');

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
        dd($listEmails);

        // Unfold
        return $listEmails;
    }

    /**
     * Gets all mailing lists for each domain
     * @param string $domain
     * @return array
     * @throws Google_Exception
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
                static fn() => $groupsManager->listGroups($args),
                'list groups',
                $domain
            );

            // Add each item from result set
            $groups[] = $groupItem->getGroups();

            // Get next token
            $pageToken = $groupItem->getNextPageToken();

            // Loop while we have results and there's a next page token
        } while ($groupItem->count() > 0 && !empty($pageToken));

        // Join
        return Arr::collapse($groups);
    }

    /**
     * @inheritdoc
     */
    public function getList(string $email): ?MailList
    {
        // Get email and domain name
        $domain = Str::after($email, '@');

        // Validate domain
        if (!\in_array($domain, \config('services.google.domains'))) {
            throw new RuntimeException("Domain {$domain} is not available for modification");
        }
        // Build query
        $query = [
            'domain' => $domain,
            'query' => sprintf("email='%s'", str_replace("'", "\\'", $email)),
            'maxResults' => 1
        ];

        // Get group manager JIT
        $groupsManager = $this->getGoogleGroupManager();

        // List groups with this e-mail address
        $result = $this->callGoogleService(
            static fn () => $groupsManager->listGroups($query),
            'get group',
            $email
        );

        // Get first group
        $group = Arr::first($result->getGroups());

        // Null if not found
        if (!$group || !$group instanceof Group) {
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
            $email
        );

        // Build and return
        return GoogleMailList::fromGoogleModel($group, $members);
    }
    /**
     * @inheritdoc
     */
    public function createList(string $email, string $name): MailList
    {
        // Get API
        $groupsManager = $this->getGoogleGroupManager();

        // Get email and domain name
        $domain = Str::after($email, '@');

        // Validate domain
        if (!\in_array($domain, \config('services.google.domains'))) {
            throw new RuntimeException("Domain {$domain} is not available for modification");
        }

        // Build group model
        $group = new Group([
            'email' => $email,
            'name' => !empty($name) ? $name : null
        ]);

        // Insert it
        $result = $this->callGoogleService(
            static fn () => $groupsManager->insert($group),
            'create group',
            $email
        );

        // Fail if required
        if (!$result || !$result instanceof Group) {
            throw new RuntimeException("Failed to create group");
        }

        // Build new item, without members
        return GoogleMailList::fromGoogleModel($group, new GroupMembers());
    }

    /**
     * Applies permissions to the given list
     * @param MailList $list
     * @param GroupSettings $permissions
     * @return void
     * @throws RuntimeException
     */
    public function applyPermissions(MailList $list, GroupSettings $permissions): void
    {
        // Get manager
        $permissionManager = $this->getGoogleGroupSettingsManager();

        // Apply changes
        $ok = $this->callGoogleService(
            static fn () => $permissionManager->patch($list->getEmail(), $permissions),
            "update permssions",
            $list->getEmail()
        );

        // Check
        if (!$ok) {
            throw new RuntimeException("Failed to set permissions on [{$list->getEmail()}]");
        }
    }

    /**
     * Updates group
     * @param MailList $list
     * @return void
     * @throws Google_Exception
     * @throws LogicException
     */
    public function save(MailList $list): void
    {
        // Get services
        $aliasManager =  $this->getGoogleGroupAliasManager();
        $memberManager = $this->getGoogleGroupMembersManager();

        // Update aliases
        foreach ($list->getChangedAliases() as $index => [$action, $alias]) {
            // Delete it
            if ($action === MailList::CHANGE_DELETE) {
                $this->callGoogleService(
                    static fn () => $aliasManager->delete($list->getServiceId(), $alias),
                    "delete alias $alias",
                    $list->getEmail()
                );

                continue;
            }

            // Check
            if ($action !== MailList::CHANGE_ADD) {
                throw new LogicException("Invalid action on [$index]: [{$action}] [{$alias}]");
            }

            // Make object
            $aliasObj = new GroupAlias([
                'alias' => $alias
            ]);

            // Add
            $this->callGoogleService(
                static fn () => $aliasManager->insert($list->getServiceId(), $aliasObj),
                "create alias $alias",
                $list->getEmail()
            );
        }

        // Update members
        foreach ($list->getChangedEmails() as $index => [$action, $email, $role]) {
            // Delete it
            if ($action === MailList::CHANGE_DELETE) {
                $this->callGoogleService(
                    static fn () => $memberManager->delete($list->getServiceId(), $email),
                    "delete member $email",
                    $list->getEmail()
                );

                continue;
            }

            // Check
            if ($action !== MailList::CHANGE_ADD && $action !== MailList::CHANGE_UPDATE) {
                throw new LogicException("Invalid action on [$index]: [{$action}] [{$alias}]");
            }

            // Prep object
            $memberObj = new GroupMember([
                'email' => $email,
                'role' => $role,
            ]);

            // Patch if update
            if ($action === MailList::CHANGE_UPDATE) {
                $this->callGoogleService(
                    static fn () => $memberManager->patch($list->getServiceId(), $email, $memberObj),
                    "update member $email",
                    $list->getEmail()
                );

                continue;
            }

            // Insert new member
            $this->callGoogleService(
                static fn () => $memberManager->insert($list->getServiceId(), $memberObj),
                "add member $email",
                $list->getEmail()
            );
        }
    }

    /**
     * Deletes the given maillist
     * @param MailList $list
     * @return void
     */
    public function delete(MailList $list): void
    {
        // Get manager
        $groupManager = $this->getGoogleGroupManager();

        // Delete group
        $res = $this->callGoogleService(
            static fn () => $groupManager->delete($list->getServiceId()),
            'delete',
            $list->getEmail()
        );

        // Check if failed
        if ($res === null) {
            throw new RuntimeException('Failed to delete group');
        }
    }
}
