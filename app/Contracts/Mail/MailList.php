<?php

declare(strict_types=1);

namespace App\Contracts\Mail;

interface MailList
{
    public const ROLE_NORMAL = 0;

    public const ROLE_ADMIN = 128;

    public const CHANGE_ADD = 'add';

    public const CHANGE_UPDATE = 'upd';

    public const CHANGE_DELETE = 'del';

    /**
     * Group email address.
     */
    public function getEmail(): string;

    /**
     * The ID the mail service will recognize this service by.
     */
    public function getServiceId(): string;

    /**
     * Returns a list of email addresses.
     *
     * @return array<array<string>>
     */
    public function listEmails(): array;

    /**
     * Adds the given email address with the given role.
     */
    public function addEmail(string $email, int $role = self::ROLE_NORMAL): void;

    /**
     * Updates the given email address to have the given role.
     */
    public function updateEmail(string $email, int $role = self::ROLE_NORMAL): void;

    /**
     * Removes the given email address from the list.
     */
    public function removeEmail(string $email): void;

    /**
     * List aliases of this mail list.
     *
     * @return array<string>
     */
    public function listAliases(): array;

    /**
     * Adds a mailing list alias.
     */
    public function addAlias(string $email): void;

    /**
     * Deletes mailing list alias.
     */
    public function deleteAlias(string $email): void;

    /**
     * Returns a sequential array with changed emails, as [MailList::CHANGE_*, email].
     *
     * @return array<array<string>>
     */
    public function getChangedEmails(): array;

    /**
     * Returns a sequential array with changed aliases, as [MailList::CHANGE_*, alias].
     *
     * @return array<array<string>>
     */
    public function getChangedAliases(): array;
}
