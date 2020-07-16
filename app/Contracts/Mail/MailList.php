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
     * Group email address
     * @return string
     */
    public function getEmail(): string;

    /**
     * The ID the mail service will recognize this service by
     * @return string
     */
    public function getServiceId(): string;

    /**
     * Returns a list of email addresses
     * @return array<array<string>>
     */
    public function listEmails(): array;

    /**
     * Adds the given email address with the given role
     * @param string $email
     * @param int $role
     * @return void
     */
    public function addEmail(string $email, int $role = self::ROLE_NORMAL): void;

    /**
     * Updates the given email address to have the given role
     * @param string $email
     * @param int $role
     * @return void
     */
    public function updateEmail(string $email, int $role = self::ROLE_NORMAL): void;

    /**
     * Removes the given email address from the list
     * @param string $email
     * @return void
     */
    public function removeEmail(string $email): void;

    /**
     * List aliases of this mail list
     * @return array<string>
     */
    public function listAliases(): array;

    /**
     * Adds a mailing list alias
     * @param string $email
     * @return void
     */
    public function addAlias(string $email): void;

    /**
     * Deletes mailing list alias
     * @param string $email
     * @return void
     */
    public function deleteAlias(string $email): void;

    /**
     * Returns a sequential array with changed emails, as [MailList::CHANGE_*, email]
     * @return array<array<string>>
     */
    public function getChangedEmails(): array;

    /**
     * Returns a sequential array with changed aliases, as [MailList::CHANGE_*, alias]
     * @return array<array<string>>
     */
    public function getChangedAliases(): array;
}
