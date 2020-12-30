<?php

declare(strict_types=1);

namespace App\Services\Mail;

use App\Contracts\Mail\MailList;
use App\Helpers\Arr;
use App\Helpers\Str;
use App\Services\Mail\Traits\ValidatesEmailRequests;
use Google_Service_Directory_Group;
use Google_Service_Directory_Member;
use Google_Service_Directory_Members;
use InvalidArgumentException;
use JsonSerializable;
use OverflowException;
use UnderflowException;

class GoogleMailList implements MailList, JsonSerializable
{
    use ValidatesEmailRequests;

    public const ROLE_NAME_ADMIN = 'MANAGER';
    public const ROLE_NAME_NORMAL = 'MEMBER';

    public static function fromGoogleModel(
        Google_Service_Directory_Group $group,
        Google_Service_Directory_Members $members
    ): self {
        $users = [];
        foreach ($members->getMembers() as $member) {
            \assert($member instanceof Google_Service_Directory_Member);
            $users[] = [
                $member->getEmail(),
                $member->getRole(),
            ];
        }

        return new static(
            $group->getEmail(),
            $group->getId() ?? $group->getEmail(),
            $users,
            $group->getAliases() ?? [],
            $group->getNonEditableAliases() ?? []
        );
    }

    protected array $memberChanges = [];
    protected array $aliasChanges = [];

    protected string $email;
    protected string $serviceId;
    private array $members;
    private array $aliases;
    private array $lockedAliases;

    /**
     * Creates a new Google mail list
     *
     * @param string $email
     * @param string $serviceId
     * @param array<array<string>> $members
     * @param array<string> $aliases
     * @param array<string> $lockedAliases
     * @return void
     * @throws InvalidArgumentException
     */
    public function __construct(
        string $email,
        string $serviceId,
        array $members,
        array $aliases,
        array $lockedAliases
    ) {
        // Check email
        if (\filter_var($email, \FILTER_VALIDATE_EMAIL) === false) {
            throw new InvalidArgumentException("List email [{$email}] is invalid");
        }

        // Check service ID
        if (empty($serviceId)) {
            throw new InvalidArgumentException("List service ID cannot be empty");
        }

        // Check members
        foreach ($members as $key => $member) {
            if (
                count($member) !== 2 ||
                !\is_string($member[0]) ||
                !\is_string($member[1]) ||
                !\filter_var($member[0], \FILTER_VALIDATE_EMAIL) ||
                !\in_array($member[1], ['MEMBER', 'MANAGER', 'OWNER'])
            ) {
                throw new InvalidArgumentException("Member at index {$key} is invalid");
            }

            // Normalize
            $members[$key][0] = Str::lower($member[0]);
        }

        // Check aliases
        foreach ($aliases as $key => $alias) {
            if (
                !\is_string($alias) ||
                !\filter_var($alias, \FILTER_VALIDATE_EMAIL)
            ) {
                throw new InvalidArgumentException("Alias at index {$key} is invalid");
            }
        }

        // Check locked aliases
        foreach ($lockedAliases as $key => $alias) {
            if (
                !\is_string($alias) ||
                !\filter_var($alias, \FILTER_VALIDATE_EMAIL)
            ) {
                throw new InvalidArgumentException("Locked alias at index {$key} is invalid");
            }
        }

        // Assign
        $this->email = $email;
        $this->serviceId = $serviceId;
        $this->members = $members;
        $this->aliases = $aliases;
        $this->lockedAliases = $lockedAliases;
    }

    /**
     * Group email address
     *
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * The ID the mail service will recognize this service by
     *
     * @return string
     */
    public function getServiceId(): string
    {
        return $this->serviceId;
    }

    /**
     * Returns a list of email addresses
     *
     * @return array<string>
     */
    public function listEmails(): array
    {
        $out = [];
        foreach ($this->members as [$email, $role]) {
            $out[] = [
                $email,
                $role === self::ROLE_NAME_ADMIN ? self::ROLE_ADMIN : self::ROLE_NORMAL,
            ];
        }
        return $out;
    }

    /**
     * Adds the given email address with the given role
     *
     * @param string $email
     * @param int $role
     * @return void
     */
    public function addEmail(string $email, int $role = self::ROLE_NORMAL): void
    {
        // Validate
        $valid = $this->normalizeItem($email, $role);

        // Check if not found
        $this->assertEmailNotExists($email);

        // Check if mutable
        $this->assertEmailMutable($email);

        // Add mutation
        $this->memberChanges[] = [self::CHANGE_ADD, $valid['email'], $valid['role']];
    }

    /**
     * Updates the given email address to have the given role
     *
     * @param string $email
     * @param int $role
     * @return void
     */
    public function updateEmail(string $email, int $role = self::ROLE_NORMAL): void
    {
        // Validate
        $valid = $this->normalizeItem($email, $role);

        // Check if found
        $this->assertEmailExists($email);

        // Check if mutable
        $this->assertEmailMutable($email);

        // Add mutation
        $this->memberChanges[] = [self::CHANGE_UPDATE, $valid['email'], $valid['role']];
    }

    /**
     * Removes the given email address from the list
     *
     * @param string $email
     * @return void
     */
    public function removeEmail(string $email): void
    {
        // Validate
        $valid = $this->normalizeItem($email, null);

        // Check if found
        $this->assertEmailExists($email);

        // Check if mutable
        $this->assertEmailMutable($email);

        // Add mutation
        $this->memberChanges[] = [self::CHANGE_DELETE, $valid['email'], null];
    }

    /**
     * List aliases of this mail list
     *
     * @return array<string>
     */
    public function listAliases(): array
    {
        return $this->aliases;
    }

    /**
     * Adds a mailing list alias
     *
     * @param string $email
     * @return void
     */
    public function addAlias(string $email): void
    {
        // Validate
        $valid = $this->normalizeItem($email, null);

        // Test against existence
        if ($this->hasAlias($valid['email'])) {
            throw new InvalidArgumentException("Alias [$email] already exists");
        }

        // Add mutation
        $this->aliasChanges[] = [self::CHANGE_ADD, $valid['email']];
    }

    /**
     * Deletes mailing list alias
     *
     * @param string $email
     * @return void
     */
    public function deleteAlias(string $email): void
    {
        // Validate
        $valid = $this->normalizeItem($email, null);

        // Test against existence
        if (!$this->hasAlias($valid['email'])) {
            throw new InvalidArgumentException("Alias [$email] not found");
        }

        // Test if locked
        if (\in_array($valid['email'], $this->lockedAliases)) {
            throw new InvalidArgumentException("Alias [$email] is locked", 420);
        }

        // Add mutation
        $this->aliasChanges[] = [self::CHANGE_DELETE, $valid['email']];
    }

    /**
     * Returns a sequential array with changed emails, as [(add|update|remove), email]
     *
     * @return array<array<string>>
     */
    public function getChangedEmails(): array
    {
        return $this->memberChanges;
    }

    /**
     * Returns a sequential array with changed aliases, as [(add|remove), alias]
     *
     * @return array<array<string>>
     */
    public function getChangedAliases(): array
    {
        return $this->aliasChanges;
    }

    /**
     * Tests if a member is present
     *
     * @param string $email
     * @return bool
     */
    public function hasMember(string $email): bool
    {
        return $this->checkEmailInList(
            Arr::pluck($this->members, 0),
            $this->memberChanges,
            $email
        );
    }

    /**
     * Tests if an alias is present
     *
     * @param string $email
     * @return bool
     */
    public function hasAlias(string $email): bool
    {
        return $this->checkEmailInList(
            $this->aliases,
            $this->aliasChanges,
            $email
        );
    }

    /**
     * Converts item to array
     *
     * @return array
     */
    public function jsonSerialize()
    {
        return [
            'email' => $this->getEmail(),
            'service-id' =>  $this->getServiceId(),
            'aliases' => $this->listAliases(),
            'members' => sprintf('[REDACTED (%d items)]', count($this->listEmails())),
        ];
    }

    /**
     * Validates email and role if given
     *
     * @param string $email
     * @param int|null $role
     * @return array
     * @throws InvalidArgumentException
     */
    protected function normalizeItem(string $email, ?int $role): array
    {
        // Check email
        if (empty($email) || \filter_var($email, \FILTER_VALIDATE_EMAIL) === false) {
            throw new InvalidArgumentException('Email address invalid');
        }

        // Map
        $validRoles = [
            self::ROLE_ADMIN => 'MANAGER',
            self::ROLE_NORMAL => 'MEMBER',
        ];

        // Check against map
        if ($role !== null && !\array_key_exists($role, $validRoles)) {
            throw new InvalidArgumentException('Role invalid');
        }

        // Add mutation
        return [
            'email' => Str::lower($email),
            'role' => $validRoles[$role ?? self::ROLE_NORMAL],
        ];
    }

    /**
     * Throws an exception if $email already exists
     *
     * @param string $email
     * @return void
     * @throws OverflowException
     */
    protected function assertEmailExists(string $email): void
    {
        if (!$this->hasMember($email)) {
            throw new UnderflowException("Email address [$email] does not exist");
        }
    }

    /**
     * Throws an exception if $email already exists
     *
     * @param string $email
     * @return void
     * @throws OverflowException
     */
    protected function assertEmailNotExists(string $email): void
    {
        if ($this->hasMember($email)) {
            throw new OverflowException("Email address [$email] already exists");
        }
    }

    /**
     * Throws an exception if $email is not mutable
     *
     * @param string $email
     * @return void
     * @throws InvalidArgumentException
     */
    protected function assertEmailMutable(string $email): void
    {
        if (!$this->canMutate($email)) {
            throw new InvalidArgumentException("Email address [$email] is not mutable");
        }
    }

    /**
     * Tests if an email address exists, taking mutations into account
     *
     * @param array $master
     * @param array $mutations
     * @param string $email
     * @return bool
     */
    private function checkEmailInList(array $master, array $mutations, string $email): bool
    {
        // Check base members
        foreach ($master as $masterEmail) {
            if ($masterEmail === $email) {
                return true;
            }
        }

        // Check mutations
        $found = false;
        foreach ($mutations as [$action, $mutationEmail]) {
            if ($mutationEmail !== $email) {
                continue;
            }

            if ($action === self::CHANGE_ADD) {
                $found = true;
            } elseif ($action === self::CHANGE_DELETE) {
                $found = false;
            }
        }

        // Return result
        return $found;
    }
}
