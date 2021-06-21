<?php

declare(strict_types=1);

namespace App\Contracts\Mail;

interface MailListHandler
{
    /**
     * Returns all mailing lists.
     *
     * @return array<MailList>
     */
    public function getAllLists(): array;

    /**
     * Returns the list with the given email address.
     */
    public function getList(string $email): ?MailList;

    /**
     * Returns a new list with the given email address. Will probably
     * throw an exception if it already exists.
     */
    public function createList(string $email, string $name): MailList;

    /**
     * Saves changes to the given MailList.
     */
    public function save(MailList $list): void;

    /**
     * Deletes the given maillist.
     */
    public function delete(MailList $list): void;
}
